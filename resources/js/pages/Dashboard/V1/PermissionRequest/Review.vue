<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { FileText, Calendar, CheckCircle, XCircle } from 'lucide-vue-next';
import type { PermissionRequestReviewProps } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestReviewProps>();

const { __ } = useTranslation();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const form = useForm({
    action: 'approve' as 'approve' | 'reject',
    review_note: '',
    rejected_status: false,
    rejected_reason: '',
});

// Computed for TiptapEditor v-model (review note - for approve)
const reviewNoteContent = computed({
    get: () => form.review_note ?? '',
    set: (val: string) => {
        form.review_note = val;
    },
});

// Computed for TiptapEditor v-model (rejected reason - for reject)
const rejectedReasonContent = computed({
    get: () => form.rejected_reason ?? '',
    set: (val: string) => {
        form.rejected_reason = val;
    },
});

const handleSubmit = () => {
    form.post(`/dashboard/permission-requests/${props.permissionRequest.uuid}/review`, {
        onSuccess: () => {
            const message = form.action === 'approve'
                ? __('Permission request approved successfully.')
                : __('Permission request rejected.');
            toast.success(message);
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getInitials = (name: string | null | undefined) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const setAction = (action: 'approve' | 'reject') => {
    form.action = action;
};

// Clear fields when switching actions
watch(() => form.action, (newAction) => {
    if (newAction === 'approve') {
        form.rejected_status = false;
        form.rejected_reason = '';
    } else {
        form.review_note = '';
    }
});
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Review Permission Request')"
        :description="__('Approve or reject this request')"
        mode="edit"
        size="lg"
        :submit-text="form.action === 'approve' ? __('Approve Request') : __('Reject Request')"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Request Summary -->
            <div class="p-4 rounded-lg border bg-muted/30 space-y-4">
                <!-- Employee Info -->
                <div class="flex items-center gap-3">
                    <Avatar class="h-10 w-10">
                        <AvatarImage :src="permissionRequest.employee?.avatar_url || ''" />
                        <AvatarFallback>{{ getInitials(permissionRequest.employee?.full_name) }}</AvatarFallback>
                    </Avatar>
                    <div>
                        <p class="font-medium">{{ permissionRequest.employee?.full_name }}</p>
                        <p class="text-xs text-muted-foreground">{{ permissionRequest.employee?.employee_code }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <FileText class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm">{{ permissionRequest.type_label }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm">{{ formatDate(permissionRequest.from_date) }} - {{ formatDate(permissionRequest.to_date) }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Badge variant="outline">{{ permissionRequest.total_days }} {{ __('day(s)') }}</Badge>
                    <Badge variant="secondary">{{ permissionRequest.status_label }}</Badge>
                </div>

                <!-- Reason -->
                <div class="border-t pt-3">
                    <p class="text-xs text-muted-foreground mb-1">{{ __('Reason') }}:</p>
                    <p class="text-sm">{{ permissionRequest.reason }}</p>
                </div>
            </div>

            <!-- Action Selection -->
            <div class="space-y-3">
                <Label>{{ __('Decision') }}</Label>
                <div class="grid grid-cols-2 gap-4">
                    <button
                        type="button"
                        @click="setAction('approve')"
                        class="flex flex-col items-center justify-center rounded-md border-2 p-4 transition-colors cursor-pointer"
                        :class="form.action === 'approve'
                            ? 'border-primary bg-primary/5'
                            : 'border-muted bg-popover hover:bg-accent hover:text-accent-foreground'"
                    >
                        <CheckCircle class="mb-3 h-6 w-6 text-green-600" />
                        <span class="text-sm font-medium">{{ __('Approve') }}</span>
                    </button>
                    <button
                        type="button"
                        @click="setAction('reject')"
                        class="flex flex-col items-center justify-center rounded-md border-2 p-4 transition-colors cursor-pointer"
                        :class="form.action === 'reject'
                            ? 'border-destructive bg-destructive/5'
                            : 'border-muted bg-popover hover:bg-accent hover:text-accent-foreground'"
                    >
                        <XCircle class="mb-3 h-6 w-6 text-red-600" />
                        <span class="text-sm font-medium">{{ __('Reject') }}</span>
                    </button>
                </div>
            </div>

            <!-- Approve: Review Note -->
            <div v-if="form.action === 'approve'" class="space-y-2">
                <Label>{{ __('Approval Note (Optional)') }}</Label>
                <TiptapEditor
                    v-model="reviewNoteContent"
                    :placeholder="__('Add any notes about your approval decision...')"
                    min-height="120px"
                    max-height="200px"
                />
                <p v-if="form.errors.review_note" class="text-xs text-destructive">
                    {{ form.errors.review_note }}
                </p>
            </div>

            <!-- Reject: Rejection fields -->
            <div v-if="form.action === 'reject'" class="space-y-4">
                <div class="flex items-center justify-between rounded-lg border p-4">
                    <div>
                        <p class="text-sm font-medium">{{ __('Provide Detailed Rejection Reason') }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ __('Enable to add a detailed explanation for the rejection') }}
                        </p>
                    </div>
                    <Switch v-model="form.rejected_status" />
                </div>

                <div v-if="form.rejected_status" class="space-y-2">
                    <Label>
                        {{ __('Rejection Reason') }} <span class="text-destructive">*</span>
                    </Label>
                    <TiptapEditor
                        v-model="rejectedReasonContent"
                        :placeholder="__('Please provide a detailed reason for rejecting this request...')"
                        min-height="150px"
                        max-height="250px"
                    />
                    <p v-if="form.errors.rejected_reason" class="text-xs text-destructive">
                        {{ form.errors.rejected_reason }}
                    </p>
                </div>
            </div>
        </div>
    </ModalForm>
</template>

<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, FileText, Calendar, User } from 'lucide-vue-next';
import type { PermissionRequestDeleteProps } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestDeleteProps>();

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

const confirmed = ref(false);

const form = useForm({
    confirmed: false,
});

watch(confirmed, () => {
    form.confirmed = confirmed.value;
});

const canSubmit = computed(() => confirmed.value === true);

const handleSubmit = () => {
    form.delete(`/dashboard/permission-requests/${props.permissionRequest.uuid}`, {
        onSuccess: () => {
            toast.success(__('Permission request deleted successfully.'));
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

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'rejected':
            return 'destructive';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Delete Permission Request')"
        :description="__('This action cannot be undone')"
        mode="delete"
        size="md"
        :submit-text="__('Delete Request')"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Request Info -->
            <div class="p-4 rounded-lg border bg-muted/30 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                        <FileText class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <p class="font-medium">{{ permissionRequest.type_label }}</p>
                        <Badge :variant="getStatusVariant(permissionRequest.status)">
                            {{ permissionRequest.status_label }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <User class="h-4 w-4" />
                    <span>{{ permissionRequest.employee?.full_name || __('Unknown') }}</span>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Calendar class="h-4 w-4" />
                    <span>{{ permissionRequest.from_date }} - {{ permissionRequest.to_date }}</span>
                    <Badge variant="outline">{{ permissionRequest.total_days }} {{ __('day(s)') }}</Badge>
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('You are about to delete this permission request') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ __('This request will be permanently removed.') }}
                    </p>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox
                    id="confirmed"
                    v-model="confirmed"
                />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">
                        {{ __('I confirm this deletion') }}
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        {{ __('I understand that this action cannot be undone.') }}
                    </p>
                </div>
            </div>

            <p v-if="form.errors.confirmed" class="text-sm text-destructive">
                {{ form.errors.confirmed }}
            </p>
        </div>
    </ModalForm>
</template>

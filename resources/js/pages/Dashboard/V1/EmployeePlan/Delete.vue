<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Calendar, FileText, Users } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

interface PlanResource {
    uuid: string;
    title: string;
    start_date: string | null;
    end_date: string | null;
    priority: string;
    status: string;
    assignees_count: number;
}

interface Props {
    plan: PlanResource;
}

const props = defineProps<Props>();

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
const form = useForm({});

const handleSubmit = () => {
    form.delete(`/dashboard/employee-plans/${props.plan.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Plan deleted successfully.'));
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
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Delete Plan')"
        :description="__('This action cannot be undone')"
        mode="delete"
        size="md"
        :submit-text="__('Delete Plan')"
        :loading="form.processing"
        :disabled="!confirmed"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <div class="space-y-3 rounded-lg border bg-muted/30 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                        <FileText class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <p class="font-medium">{{ props.plan.title }}</p>
                        <Badge variant="outline">{{ __(props.plan.priority) }}</Badge>
                        <Badge class="ml-1">{{ __(props.plan.status) }}</Badge>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Calendar class="h-4 w-4" />
                    <span>{{ props.plan.start_date }} – {{ props.plan.end_date }}</span>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Users class="h-4 w-4" />
                    <span>{{ props.plan.assignees_count }} {{ __('assignees') }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('You are about to delete this plan') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ __('All assignments will also be soft-deleted. Both can be restored from trash.') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox id="confirmed" v-model="confirmed" />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">
                        {{ __('I confirm this deletion') }}
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        {{ __('I understand this action removes the plan and its assignments.') }}
                    </p>
                </div>
            </div>
        </div>
    </ModalForm>
</template>

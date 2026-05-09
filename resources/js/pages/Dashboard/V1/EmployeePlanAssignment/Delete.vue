<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, FileText, User } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

interface AssignmentResource {
    uuid: string;
    status: string;
    status_label: string;
    plan?: { title: string };
    employee?: { full_name: string; employee_code: string };
}

interface Props {
    assignment: AssignmentResource;
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
    form.delete(`/dashboard/employee-plan-assignments/${props.assignment.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Assignment removed successfully.'));
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
        :title="__('Remove Assignment')"
        :description="__('This action cannot be undone')"
        mode="delete"
        size="md"
        :submit-text="__('Remove')"
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
                        <p class="font-medium">{{ props.assignment.plan?.title }}</p>
                        <Badge>{{ props.assignment.status_label }}</Badge>
                    </div>
                </div>

                <div v-if="props.assignment.employee" class="flex items-center gap-2 text-sm text-muted-foreground">
                    <User class="h-4 w-4" />
                    <span>
                        {{ props.assignment.employee.full_name }}
                        ({{ props.assignment.employee.employee_code }})
                    </span>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('You are about to remove this assignment') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ __('The plan and the employee remain — only the link is soft-deleted.') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox id="confirmed" v-model="confirmed" />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">
                        {{ __('I confirm this removal') }}
                    </Label>
                </div>
            </div>
        </div>
    </ModalForm>
</template>

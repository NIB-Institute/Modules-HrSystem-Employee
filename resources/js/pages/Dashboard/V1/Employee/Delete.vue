<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { AlertTriangle } from 'lucide-vue-next';
import type { EmployeeDeleteProps } from '@employee/types';

const props = defineProps<EmployeeDeleteProps>();

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

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handleSubmit = () => {
    form.delete(`/dashboard/employees/${props.employee.uuid}`, {
        onSuccess: () => {
            toast.success('Employee deleted successfully.');
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
        title="Delete Employee"
        description="This action cannot be undone"
        mode="delete"
        size="md"
        submit-text="Delete Employee"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Employee Info -->
            <div class="flex items-center gap-4 p-4 rounded-lg border bg-muted/30">
                <Avatar class="h-12 w-12">
                    <AvatarImage :src="employee.avatar_url || ''" :alt="employee.full_name" />
                    <AvatarFallback>{{ getInitials(employee.full_name) }}</AvatarFallback>
                </Avatar>
                <div>
                    <p class="font-medium">{{ employee.full_name }}</p>
                    <p class="text-sm text-muted-foreground">{{ employee.employee_code }}</p>
                    <p v-if="employee.job_title" class="text-sm text-muted-foreground">{{ employee.job_title }}</p>
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        You are about to delete this employee
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <strong>{{ employee.full_name }}</strong> will be permanently removed from the system.
                    </p>
                    <p v-if="employee.courses_count" class="text-sm text-muted-foreground">
                        This employee is associated with <strong>{{ employee.courses_count }}</strong> course(s).
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
                        I confirm this deletion
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that this action cannot be undone.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.confirmed" class="text-sm text-destructive">
                {{ form.errors.confirmed }}
            </p>
        </div>
    </ModalForm>
</template>

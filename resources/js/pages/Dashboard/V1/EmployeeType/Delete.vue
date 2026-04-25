<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { AlertTriangle, Tags } from 'lucide-vue-next';
import type { EmployeeTypeDeleteProps } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<EmployeeTypeDeleteProps>();

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
    form.delete(`/dashboard/employee-types/${props.employeeType.uuid}`, {
        onSuccess: () => {
            toast.success(__('Employee type deleted successfully.'));
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
        :title="__('Delete Employee Type')"
        :description="__('This action cannot be undone')"
        mode="delete"
        size="md"
        :submit-text="__('Delete Type')"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Type Info -->
            <div class="flex items-center gap-4 p-4 rounded-lg border bg-muted/30">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                    <Tags class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <p class="font-medium">{{ employeeType.name }}</p>
                    <p v-if="employeeType.description" class="text-sm text-muted-foreground">
                        {{ employeeType.description }}
                    </p>
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('You are about to delete this employee type') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <strong>{{ employeeType.name }}</strong> {{ __('will be permanently removed from the system.') }}
                    </p>
                    <p v-if="employeeType.employees_count && employeeType.employees_count > 0" class="text-sm text-destructive">
                        {{ __('Warning: This type has') }} <strong>{{ employeeType.employees_count }}</strong> {{ __('employee(s) assigned to it.') }}
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

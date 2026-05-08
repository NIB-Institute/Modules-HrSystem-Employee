<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import EmployeePlanForm from '@employee/Components/Dashboard/EmployeePlanForm.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

interface EmployeeOption {
    id: number;
    full_name: string;
    employee_code: string;
}

interface PlanResource {
    id: number;
    uuid: string;
    employee_id: number | null;
    title: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    start_time: string | null;
    end_time: string | null;
    priority: string;
    location: string | null;
    schedule_mode: string;
    is_recurring: boolean;
    recurrence_type: string | null;
    status: string;
}

interface Props {
    plan: PlanResource;
    employees: EmployeeOption[];
    priorities: string[];
    scheduleModes: string[];
    recurrenceTypes: string[];
    statuses: string[];
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

const form = useForm({
    employee_id: props.plan.employee_id,
    title: props.plan.title ?? '',
    description: props.plan.description ?? '',
    start_date: props.plan.start_date ?? '',
    end_date: props.plan.end_date ?? '',
    start_time: props.plan.start_time ?? '',
    end_time: props.plan.end_time ?? '',
    priority: props.plan.priority,
    location: props.plan.location ?? '',
    schedule_mode: props.plan.schedule_mode,
    is_recurring: props.plan.is_recurring,
    recurrence_type: props.plan.recurrence_type ?? '',
    status: props.plan.status,
});

const isFormInvalid = computed(() =>
    !form.employee_id ||
    !form.title.trim() ||
    !form.start_date ||
    !form.end_date ||
    !form.priority ||
    !form.schedule_mode
);

const handleSubmit = () => {
    form.put(`/dashboard/employee-plans/${props.plan.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Employee plan updated successfully.'));
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
        :title="__('Edit Employee Plan')"
        :description="__('Update plan details')"
        mode="edit"
        size="xl"
        :submit-text="__('Save Changes')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeePlanForm
            :form="form"
            mode="edit"
            :employees="props.employees"
            :priorities="props.priorities"
            :schedule-modes="props.scheduleModes"
            :recurrence-types="props.recurrenceTypes"
        />
    </ModalForm>
</template>

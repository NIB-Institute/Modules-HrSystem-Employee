<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import EmployeePlanAssignmentForm from '@employee/Components/Dashboard/EmployeePlanAssignmentForm.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

interface PlanOption {
    id: number;
    uuid: string;
    title: string;
}

interface EmployeeOption {
    id: number;
    full_name: string;
    employee_code: string;
}

interface AvailabilityOption {
    id: number;
    employee_id: number;
    day_of_week: string;
    start_time: string | null;
    end_time: string | null;
}

interface Props {
    plans: PlanOption[];
    employees: EmployeeOption[];
    availabilities: AvailabilityOption[];
    statuses: string[];
    selectedPlanId?: number | null;
    selectedEmployeeId?: number | null;
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
    employee_plan_id: props.selectedPlanId ?? null as number | null,
    employee_id: props.selectedEmployeeId ?? null as number | null,
    employee_availability_id: null as number | null,
    status: 'assigned',
    started_at: '',
    completed_at: '',
    notes: '',
});

const isFormInvalid = computed(() => !form.employee_plan_id || !form.employee_id);

const handleSubmit = () => {
    form.post('/dashboard/employee-plan-assignments', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Employee assigned successfully.'));
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
        :title="__('Assign Employee to Plan')"
        :description="__('Link a single employee to a plan')"
        mode="create"
        size="lg"
        :submit-text="__('Assign')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeePlanAssignmentForm
            :form="form"
            mode="create"
            :plans="props.plans"
            :employees="props.employees"
            :availabilities="props.availabilities"
            :statuses="props.statuses"
        />
    </ModalForm>
</template>

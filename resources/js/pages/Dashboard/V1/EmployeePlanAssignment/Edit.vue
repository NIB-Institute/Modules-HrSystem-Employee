<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import EmployeePlanAssignmentForm from '@employee/Components/Dashboard/EmployeePlanAssignmentForm.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

interface AssignmentResource {
    id: number;
    uuid: string;
    employee_plan_id: number;
    employee_id: number;
    employee_availability_id: number | null;
    status: string;
    started_at: string | null;
    completed_at: string | null;
    notes: string | null;
    plan?: {
        id: number;
        uuid: string;
        title: string;
        start_date?: string | null;
        end_date?: string | null;
        start_time?: string | null;
        end_time?: string | null;
    };
    employee?: { full_name: string; employee_code: string };
}

interface AvailabilityOption {
    id: number;
    employee_id: number;
    day_of_week: string;
    start_time: string | null;
    end_time: string | null;
}

interface Props {
    assignment: AssignmentResource;
    availabilities: AvailabilityOption[];
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
    employee_plan_id: props.assignment.employee_plan_id,
    employee_id: props.assignment.employee_id,
    employee_availability_id: props.assignment.employee_availability_id,
    status: props.assignment.status,
    started_at: props.assignment.started_at?.slice(0, 10) ?? '',
    completed_at: props.assignment.completed_at?.slice(0, 10) ?? '',
    notes: props.assignment.notes ?? '',
});

const isFormInvalid = computed(() => !form.status);

const handleSubmit = () => {
    form.put(`/dashboard/employee-plan-assignments/${props.assignment.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Assignment updated successfully.'));
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
        :title="__('Edit Assignment')"
        :description="props.assignment.employee?.full_name + ' — ' + props.assignment.plan?.title"
        mode="edit"
        size="lg"
        :submit-text="__('Save Changes')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeePlanAssignmentForm
            :form="form"
            mode="edit"
            :plan="props.assignment.plan"
            :availabilities="props.availabilities"
            :statuses="props.statuses"
        />
    </ModalForm>
</template>

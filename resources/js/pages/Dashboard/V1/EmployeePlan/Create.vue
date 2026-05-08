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

interface Props {
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
    employee_id: null as number | null,
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    priority: 'medium',
    location: '',
    schedule_mode: 'single',
    is_recurring: false,
    recurrence_type: '',
    status: 'scheduled',
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
    form.post('/dashboard/employee-plans', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Employee plan created successfully.'));
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
        :title="__('New Employee Plan')"
        :description="__('Schedule a plan or task for an employee')"
        mode="create"
        size="xl"
        :submit-text="__('Create Plan')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeePlanForm
            :form="form"
            mode="create"
            :employees="props.employees"
            :priorities="props.priorities"
            :schedule-modes="props.scheduleModes"
            :recurrence-types="props.recurrenceTypes"
        />
    </ModalForm>
</template>

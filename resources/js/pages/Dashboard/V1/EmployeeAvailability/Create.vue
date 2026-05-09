<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import EmployeeAvailabilityForm from '@employee/Components/Dashboard/EmployeeAvailabilityForm.vue';
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
    days: string[];
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
    employee_id: props.selectedEmployeeId ?? null as number | null,
    day_of_week: 'monday',
    start_time: '09:00',
    end_time: '17:00',
    is_active: true,
    notes: '',
});

const isFormInvalid = computed(() =>
    !form.employee_id ||
    !form.day_of_week ||
    !form.start_time ||
    !form.end_time
);

const handleSubmit = () => {
    form.post('/dashboard/employee-availabilities', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Availability slot created successfully.'));
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
        :title="__('New Availability Slot')"
        :description="__('Define when this employee is available to work')"
        mode="create"
        size="lg"
        :submit-text="__('Create Slot')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeeAvailabilityForm
            :form="form"
            mode="create"
            :employees="props.employees"
            :days="props.days"
        />
    </ModalForm>
</template>

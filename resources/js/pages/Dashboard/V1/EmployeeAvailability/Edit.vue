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

interface AvailabilityResource {
    id: number;
    uuid: string;
    employee_id: number | null;
    day_of_week: string;
    start_time: string | null;
    end_time: string | null;
    is_active: boolean;
    notes: string | null;
}

interface Props {
    availability: AvailabilityResource;
    employees: EmployeeOption[];
    days: string[];
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
    employee_id: props.availability.employee_id,
    day_of_week: props.availability.day_of_week,
    start_time: props.availability.start_time ?? '',
    end_time: props.availability.end_time ?? '',
    is_active: props.availability.is_active,
    notes: props.availability.notes ?? '',
});

const isFormInvalid = computed(() =>
    !form.employee_id ||
    !form.day_of_week ||
    !form.start_time ||
    !form.end_time
);

const handleSubmit = () => {
    form.put(`/dashboard/employee-availabilities/${props.availability.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Availability slot updated successfully.'));
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
        :title="__('Edit Availability Slot')"
        :description="__('Update the recurring availability for this employee')"
        mode="edit"
        size="lg"
        :submit-text="__('Save Changes')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <EmployeeAvailabilityForm
            :form="form"
            mode="edit"
            :employees="props.employees"
            :days="props.days"
        />
    </ModalForm>
</template>

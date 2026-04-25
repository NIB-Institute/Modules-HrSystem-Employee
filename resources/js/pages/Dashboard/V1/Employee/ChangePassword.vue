<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { router, useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import ChangePasswordForm from '@employee/Components/Dashboard/ChangePasswordForm.vue';

interface EmployeeUser {
    id: number;
    name: string;
    email: string;
}

interface EmployeeData {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    email: string | null;
    avatar_url: string | null;
    user: EmployeeUser | null;
}

interface EmployeeOption {
    value: string;
    label: string;
    description: string;
    avatar_url: string | null;
}

const props = defineProps<{
    employee: EmployeeData;
    employeeOptions: EmployeeOption[];
}>();

const { show, close, redirect } = useModal();

// Selected employee UUID for the searchable select
const selectedEmployeeUuid = ref<string | number | null>(props.employee.uuid);

// Watch for selection changes and navigate to that employee's change password page
watch(selectedEmployeeUuid, (newUuid) => {
    if (newUuid && newUuid !== props.employee.uuid) {
        router.visit(`/dashboard/employees/${newUuid}/change-password`);
    }
});

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
    password: '',
    password_confirmation: '',
});

const isFormInvalid = computed(() => {
    return !form.password || !form.password_confirmation || form.password.length < 8;
});

const handleSubmit = () => {
    form.put(`/dashboard/employees/${props.employee.uuid}/change-password`, {
        onSuccess: () => {
            close();
            redirect();
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
        title="Change Password"
        :description="`Update login credentials for ${employee.full_name}`"
        mode="edit"
        size="md"
        submit-text="Change Password"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ChangePasswordForm
            :employee="employee"
            :employee-options="employeeOptions"
            :form="form"
            v-model:selected-employee-uuid="selectedEmployeeUuid"
        />
    </ModalForm>
</template>

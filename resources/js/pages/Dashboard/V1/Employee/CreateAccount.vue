<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { router, useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import CreateAccountForm from '@employee/Components/Dashboard/CreateAccountForm.vue';

interface EmployeeData {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    email: string | null;
    phone_number: string | null;
    avatar_url: string | null;
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

// Watch for selection changes and navigate to that employee's create account page
watch(selectedEmployeeUuid, (newUuid) => {
    if (newUuid && newUuid !== props.employee.uuid) {
        router.visit(`/dashboard/employees/${newUuid}/create-account`);
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

// Determine default login method based on available data
const getDefaultLoginMethod = () => {
    if (props.employee.email) return 'email';
    if (props.employee.phone_number) return 'phone';
    return 'email';
};

const form = useForm({
    password: '',
    password_confirmation: '',
    login_method: getDefaultLoginMethod(),
});

const isFormInvalid = computed(() => {
    return !form.password || !form.password_confirmation || form.password.length < 8;
});

const handleSubmit = () => {
    form.post(`/dashboard/employees/${props.employee.uuid}/create-account`, {
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
        title="Create Login Account"
        description="Create a login account for an employee to access self-service features."
        mode="create"
        size="md"
        submit-text="Create Account"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <CreateAccountForm
            :employee="employee"
            :employee-options="employeeOptions"
            :form="form"
            v-model:selected-employee-uuid="selectedEmployeeUuid"
        />
    </ModalForm>
</template>

<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import EmployeePlanForm from '@employee/Components/Dashboard/EmployeePlanForm.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
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
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    priority: 'medium',
    location: '',
    telegram_group_chat_id: '',
    telegram_group_name: '',
    schedule_mode: 'single',
    is_recurring: false,
    recurrence_type: '',
    status: 'scheduled',
    valid_for_months: null as number | null,
});

const isFormInvalid = computed(() =>
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
            toast.success(__('Plan created successfully.'));
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
        :title="__('New Plan')"
        :description="__('Create a plan template — assign employees in the next step')"
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
            :priorities="props.priorities"
            :schedule-modes="props.scheduleModes"
            :recurrence-types="props.recurrenceTypes"
        />
    </ModalForm>
</template>

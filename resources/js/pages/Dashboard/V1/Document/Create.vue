<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import DocumentForm from '@employee/Components/Dashboard/DocumentForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { useTranslation } from '@/composables/useTranslation';

const { __ } = useTranslation();
const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => { if (!val) { close(); redirect(); } },
});

const form = useForm<{ name: string; description: string; file: File | null }>({
    name: '',
    description: '',
    file: null,
});

const isFormInvalid = computed(() => !form.name.trim() || !form.file);

const handleSubmit = () => {
    form.post('/dashboard/documents', {
        forceFormData: true,
        onSuccess: () => {
            toast.success(__('Document uploaded successfully.'));
            setTimeout(() => { close(); redirect(); }, 100);
        },
    });
};

const handleCancel = () => { close(); redirect(); };
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Upload Document')"
        :description="__('Add a new file to the library')"
        mode="create"
        size="lg"
        :submit-text="__('Upload')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <DocumentForm :form="form" mode="create" />
    </ModalForm>
</template>

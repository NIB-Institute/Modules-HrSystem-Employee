<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import DocumentForm from '@employee/Components/Dashboard/DocumentForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { useTranslation } from '@/composables/useTranslation';

interface DocumentModel {
    uuid: string;
    name: string;
    original_filename: string;
    human_size: string;
    extension: string | null;
    description: string | null;
}

interface Props {
    document: DocumentModel;
}

const props = defineProps<Props>();
const { __ } = useTranslation();
const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => { if (!val) { close(); redirect(); } },
});

// _method=put because multipart PUT isn't natively supported by HTML forms.
// Laravel reads _method and routes the request to the PUT handler.
const form = useForm<{ name: string; description: string; file: File | null; _method: 'put' }>({
    name: props.document.name,
    description: props.document.description ?? '',
    file: null,
    _method: 'put',
});

const isFormInvalid = computed(() => !form.name.trim());

const handleSubmit = () => {
    form.post(`/dashboard/documents/${props.document.uuid}`, {
        forceFormData: true,
        onSuccess: () => {
            toast.success(__('Document updated successfully.'));
            setTimeout(() => { close(); redirect(); }, 100);
        },
    });
};

const handleCancel = () => { close(); redirect(); };
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Edit Document')"
        :description="__('Update the name, description, or replace the file')"
        mode="edit"
        size="lg"
        :submit-text="__('Save Changes')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <DocumentForm :form="form" mode="edit" :document="document" />
    </ModalForm>
</template>

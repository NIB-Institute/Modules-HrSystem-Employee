<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm, ExportForm } from '@/components/shared';
import type { ExportColumn, ExportFormState } from '@/components/shared';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<{
    exportColumns: ExportColumn[];
}>();

const { __ } = useTranslation();
const { show, close, redirect } = useModal();
const page = usePage<{ filters?: Record<string, unknown> }>();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const state = ref<ExportFormState>({
    mode: 'export',
    format: 'xlsx',
    columns: [],
});

const canSubmit = computed(() => state.value.columns.length > 0);

const submitText = computed(() =>
    state.value.mode === 'template'
        ? __('Download Template')
        : __('Export'),
);

const titleText = computed(() =>
    state.value.mode === 'template'
        ? __('Download Employees Template')
        : __('Export Employees'),
);

const handleSubmit = () => {
    if (!canSubmit.value) return;

    const params = new URLSearchParams();
    params.set('mode', state.value.mode);
    params.set('format', state.value.format);
    state.value.columns.forEach((k) => params.append('columns[]', k));

    if (state.value.mode === 'export') {
        const filters = (page.props.filters as Record<string, unknown>) ?? {};
        Object.entries(filters).forEach(([k, v]) => {
            if (v === null || v === undefined || v === '') return;
            params.append(`filters[${k}]`, String(v));
        });
    }

    // Trigger the file download. The browser handles it; close the modal.
    window.location.assign(`/dashboard/employees/export?${params.toString()}`);

    setTimeout(() => {
        close();
        redirect();
    }, 100);
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="titleText"
        :description="__('Choose the columns and format for your file.')"
        mode="create"
        size="lg"
        :submit-text="submitText"
        :loading="false"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <ExportForm
            v-model:state="state"
            :available-columns="props.exportColumns"
        />
    </ModalForm>
</template>

<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { AlertTriangle, FileText } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

interface DocumentModel {
    uuid: string;
    name: string;
    original_filename: string;
    human_size: string;
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

const confirmed = ref(false);
const form = useForm({ confirmed: false });
watch(confirmed, () => { form.confirmed = confirmed.value; });

const canSubmit = computed(() => confirmed.value === true);

const handleSubmit = () => {
    form.delete(`/dashboard/documents/${props.document.uuid}`, {
        onSuccess: () => {
            toast.success(__('Document deleted successfully.'));
            setTimeout(() => { close(); redirect(); }, 100);
        },
    });
};

const handleCancel = () => { close(); redirect(); };
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Delete Document')"
        :description="__('This will permanently remove the file')"
        mode="delete"
        size="md"
        :submit-text="__('Delete Document')"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <div class="flex items-center gap-4 rounded-lg border bg-muted/30 p-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                    <FileText class="h-6 w-6 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">{{ document.name }}</p>
                    <p class="text-sm text-muted-foreground truncate">{{ document.original_filename }} · {{ document.human_size }}</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('The file will be removed from disk and cannot be downloaded again.') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox id="confirmed" v-model="confirmed" />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">{{ __('I confirm this deletion') }}</Label>
                    <p class="text-sm text-muted-foreground">{{ __('I understand that this action cannot be undone.') }}</p>
                </div>
            </div>
        </div>
    </ModalForm>
</template>

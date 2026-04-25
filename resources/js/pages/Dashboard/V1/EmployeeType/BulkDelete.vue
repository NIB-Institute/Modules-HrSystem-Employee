<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { AlertTriangle, Tags } from 'lucide-vue-next';
import type { EmployeeTypeModel } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

interface BulkDeleteProps {
    employeeTypes: EmployeeTypeModel[];
}

const props = defineProps<BulkDeleteProps>();

const { __ } = useTranslation();

const { show, close, redirect } = useModal();
const confirmed = ref(false);

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

watch(confirmed, () => {
    form.clearErrors();
});

const canSubmit = computed(() => confirmed.value === true);

const form = useForm({
    uuids: props.employeeTypes.map((t) => t.uuid),
});

const handleSubmit = () => {
    form.delete('/dashboard/employee-types/bulk-delete', {
        preserveScroll: true,
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
        :title="__('Delete') + ' ' + employeeTypes.length + ' ' + __('Employee Type(s)')"
        :description="__('This action will move the selected employee types to trash')"
        mode="delete"
        size="md"
        :submit-text="__('Delete') + ' ' + employeeTypes.length + ' ' + __('Employee Type(s)')"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <!-- Warning -->
        <div class="rounded-lg border border-destructive/20 bg-destructive/5 p-4">
            <div class="flex items-start gap-3">
                <AlertTriangle class="h-5 w-5 text-destructive mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-destructive">{{ __('Warning') }}</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        {{ __('You are about to delete') }} {{ employeeTypes.length }} {{ __('Employee Type(s)') }}.
                        {{ __('This will move them to trash.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items to delete -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">{{ __('Items to delete:') }}</Label>
            <div class="max-h-[200px] overflow-y-auto rounded-md border p-3 space-y-2">
                <div
                    v-for="type in employeeTypes"
                    :key="type.uuid"
                    class="flex items-center gap-3 p-2 rounded-md bg-muted/50"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-primary/10">
                        <Tags class="h-4 w-4 text-primary" />
                    </div>
                    <div>
                        <span class="font-medium">{{ type.name }}</span>
                        <span v-if="type.employees_count" class="text-xs text-muted-foreground ml-2">
                            ({{ type.employees_count }} {{ __('employees') }})
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Checkbox -->
        <div class="flex items-center gap-2">
            <Checkbox
                id="confirm-delete"
                :model-value="confirmed"
                @update:model-value="confirmed = $event as boolean"
            />
            <Label for="confirm-delete" class="text-sm cursor-pointer">
                {{ __('I understand that this will delete') }} {{ employeeTypes.length }} {{ __('Employee Type(s)') }}
            </Label>
        </div>
    </ModalForm>
</template>

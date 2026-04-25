<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Switch } from '@/components/ui/switch';
import { employeeTypeSchema } from '@employee/validation/employeeTypeSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { EmployeeTypeFormData } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

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

const form = useForm<EmployeeTypeFormData>({
    name: '',
    description: '',
    time_start: '',
    time_end: '',
    status: true,
});

const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    employeeTypeSchema,
    ['name']
);

const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    time_start: form.time_start || null,
    time_end: form.time_end || null,
    status: form.status,
});

watch(() => form.name, () => {
    if (form.name) {
        validateForm(getFormData());
    }
});

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/employee-types', {
            onSuccess: () => {
                toast.success(__('Employee type created successfully.'));
                setTimeout(() => {
                    close();
                    redirect();
                }, 100);
            },
        });
    });
};

const handleCancel = () => {
    close();
    redirect();
};

const isActive = computed({
    get: () => form.status,
    set: (value: boolean) => {
        form.status = value;
    },
});
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Create Employee Type')"
        :description="__('Add a new employee type category')"
        mode="create"
        size="lg"
        :submit-text="__('Create Type')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-4">
            <!-- Name -->
            <div class="space-y-2">
                <Label for="name">
                    {{ __('Name') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    :placeholder="__('Enter type name')"
                    :class="{ 'border-destructive': form.errors.name }"
                />
                <p v-if="form.errors.name" class="text-xs text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <Label for="description">{{ __('Description') }}</Label>
                <TiptapEditor
                    v-model="form.description"
                    :placeholder="__('Enter type description...')"
                    min-height="120px"
                    max-height="250px"
                />
                <p v-if="form.errors.description" class="text-xs text-destructive">
                    {{ form.errors.description }}
                </p>
            </div>

            <!-- Time Range -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="time_start">{{ __('Start Time') }}</Label>
                    <Input
                        id="time_start"
                        type="time"
                        v-model="form.time_start"
                        :class="{ 'border-destructive': form.errors.time_start }"
                    />
                    <p v-if="form.errors.time_start" class="text-xs text-destructive">
                        {{ form.errors.time_start }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="time_end">{{ __('End Time') }}</Label>
                    <Input
                        id="time_end"
                        type="time"
                        v-model="form.time_end"
                        :class="{ 'border-destructive': form.errors.time_end }"
                    />
                    <p v-if="form.errors.time_end" class="text-xs text-destructive">
                        {{ form.errors.time_end }}
                    </p>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-between rounded-lg border p-4">
                <div>
                    <p class="text-sm font-medium">{{ __('Active Status') }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ isActive ? __('Type will be active') : __('Type will be inactive') }}
                    </p>
                </div>
                <Switch v-model="isActive" />
            </div>
        </div>
    </ModalForm>
</template>

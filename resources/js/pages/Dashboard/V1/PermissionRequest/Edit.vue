<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { permissionRequestSchema } from '@employee/validation/permissionRequestSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { PermissionRequestEditProps, PermissionRequestType } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestEditProps>();

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
    employee_id: props.permissionRequest.employee_id,
    type: props.permissionRequest.type as PermissionRequestType,
    reason: props.permissionRequest.reason,
    from_date: props.permissionRequest.from_date,
    to_date: props.permissionRequest.to_date,
});

const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    permissionRequestSchema,
    ['employee_id', 'type', 'reason', 'from_date', 'to_date']
);

const getFormData = () => ({
    employee_id: form.employee_id,
    type: form.type,
    reason: form.reason,
    from_date: form.from_date,
    to_date: form.to_date,
});

watch([() => form.reason, () => form.from_date, () => form.to_date], () => {
    validateForm(getFormData());
});

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.put(`/dashboard/permission-requests/${props.permissionRequest.uuid}`, {
            onSuccess: () => {
                toast.success(__('Permission request updated successfully.'));
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

const handleTypeChange = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    form.type = (value as PermissionRequestType) || 'leave';
};

const getTypeDescription = (type: PermissionRequestType) => {
    return props.typeDescriptions[type] || '';
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Edit Permission Request')"
        :description="__('Update your permission request details')"
        mode="edit"
        size="lg"
        :submit-text="__('Update Request')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-4">
            <!-- Employee (Read-only) -->
            <div class="space-y-2">
                <Label>{{ __('Employee') }}</Label>
                <div class="flex items-center gap-3 p-3 rounded-lg border bg-muted/30">
                    <div>
                        <p class="font-medium">{{ props.permissionRequest.employee?.full_name }}</p>
                        <p class="text-xs text-muted-foreground">{{ props.permissionRequest.employee?.employee_code }}</p>
                    </div>
                </div>
            </div>

            <!-- Type -->
            <div class="space-y-2">
                <Label for="type">
                    {{ __('Request Type') }} <span class="text-destructive">*</span>
                </Label>
                <Select :model-value="form.type" @update:model-value="handleTypeChange">
                    <SelectTrigger :class="{ 'border-destructive': form.errors.type }">
                        <SelectValue :placeholder="__('Select type')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="(label, key) in props.types"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.type" class="text-xs text-muted-foreground">
                    {{ getTypeDescription(form.type) }}
                </p>
                <p v-if="form.errors.type" class="text-xs text-destructive">
                    {{ form.errors.type }}
                </p>
            </div>

            <!-- Date Range -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="from_date">
                        {{ __('From Date') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="from_date"
                        type="date"
                        v-model="form.from_date"
                        :class="{ 'border-destructive': form.errors.from_date }"
                    />
                    <p v-if="form.errors.from_date" class="text-xs text-destructive">
                        {{ form.errors.from_date }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="to_date">
                        {{ __('To Date') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="to_date"
                        type="date"
                        v-model="form.to_date"
                        :class="{ 'border-destructive': form.errors.to_date }"
                    />
                    <p v-if="form.errors.to_date" class="text-xs text-destructive">
                        {{ form.errors.to_date }}
                    </p>
                </div>
            </div>

            <!-- Reason -->
            <div class="space-y-2">
                <Label for="reason">
                    {{ __('Reason') }} <span class="text-destructive">*</span>
                </Label>
                <Textarea
                    id="reason"
                    v-model="form.reason"
                    :placeholder="__('Please provide a detailed reason for your request...')"
                    rows="4"
                    :class="{ 'border-destructive': form.errors.reason }"
                />
                <p class="text-xs text-muted-foreground">
                    {{ __('Minimum 10 characters required') }}
                </p>
                <p v-if="form.errors.reason" class="text-xs text-destructive">
                    {{ form.errors.reason }}
                </p>
            </div>
        </div>
    </ModalForm>
</template>

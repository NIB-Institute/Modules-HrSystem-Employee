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
import type { PermissionRequestCreateProps, PermissionRequestType } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestCreateProps>();

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
    employee_id: props.selectedEmployeeId || null as number | null,
    type: 'leave' as PermissionRequestType,
    reason: '',
    from_date: '',
    to_date: '',
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

watch([() => form.employee_id, () => form.reason, () => form.from_date, () => form.to_date], () => {
    if (form.employee_id && form.reason) {
        validateForm(getFormData());
    }
});

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/permission-requests', {
            onSuccess: () => {
                toast.success(__('Permission request submitted successfully.'));
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

const handleEmployeeChange = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    form.employee_id = value ? Number(value) : null;
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
        :title="__('New Permission Request')"
        :description="__('Submit a leave or permission request')"
        mode="create"
        size="lg"
        :submit-text="__('Submit Request')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-4">
            <!-- Employee -->
            <div class="space-y-2">
                <Label for="employee_id">
                    {{ __('Employee') }} <span class="text-destructive">*</span>
                </Label>
                <Select :model-value="form.employee_id?.toString() || ''" @update:model-value="handleEmployeeChange">
                    <SelectTrigger :class="{ 'border-destructive': form.errors.employee_id }">
                        <SelectValue :placeholder="__('Select Employee')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="employee in props.employees"
                            :key="employee.id"
                            :value="employee.id.toString()"
                        >
                            {{ employee.full_name }} ({{ employee.employee_code }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.employee_id" class="text-xs text-destructive">
                    {{ form.errors.employee_id }}
                </p>
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

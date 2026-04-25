<script setup lang="ts">
import { ModalForm, SearchableSelect } from '@/components/shared';
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
import type { AttendanceCreateProps, AttendanceStatus } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<AttendanceCreateProps>();

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
    employee_id: null as number | null,
    attendance_date: new Date().toISOString().split('T')[0],
    check_in_time: '',
    check_out_time: '',
    status: 'present' as AttendanceStatus,
    department_id: null as number | null,
    notes: '',
});

const isFormInvalid = computed(() => {
    return !form.employee_id || !form.attendance_date || !form.status;
});

const handleSubmit = () => {
    form.post('/dashboard/attendances', {
        onSuccess: () => {
            toast.success(__('Attendance recorded successfully.'));
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

const handleEmployeeChange = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    if (typeof value === 'number') {
        form.employee_id = value;
    }
};

const handleStatusChange = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    if (typeof value === 'string') {
        form.status = value as AttendanceStatus;
    }
};

const employeeOptions = computed(() => {
    return props.employeeOptions.map(emp => ({
        value: emp.value,
        label: emp.label,
        description: emp.department,
    }));
});
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Manual Attendance Entry')"
        :description="__('Record attendance manually for an employee')"
        mode="create"
        size="lg"
        :submit-text="__('Record Attendance')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
            <!-- Employee -->
            <div class="space-y-2">
                <Label for="employee_id">
                    {{ __('Employee') }} <span class="text-destructive">*</span>
                </Label>
                <SearchableSelect
                    :model-value="form.employee_id"
                    :options="employeeOptions"
                    :placeholder="__('Select employee')"
                    :search-placeholder="__('Search employees...')"
                    :empty-message="__('No employees found.')"
                    @update:model-value="handleEmployeeChange"
                />
                <p v-if="form.errors.employee_id" class="text-xs text-destructive">
                    {{ form.errors.employee_id }}
                </p>
            </div>

            <!-- Date -->
            <div class="space-y-2">
                <Label for="attendance_date">
                    {{ __('Date') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="attendance_date"
                    type="date"
                    v-model="form.attendance_date"
                    :class="{ 'border-destructive': form.errors.attendance_date }"
                />
                <p v-if="form.errors.attendance_date" class="text-xs text-destructive">
                    {{ form.errors.attendance_date }}
                </p>
            </div>

            <!-- Time -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="check_in_time">{{ __('Check In Time') }}</Label>
                    <Input
                        id="check_in_time"
                        type="time"
                        v-model="form.check_in_time"
                    />
                </div>
                <div class="space-y-2">
                    <Label for="check_out_time">{{ __('Check Out Time') }}</Label>
                    <Input
                        id="check_out_time"
                        type="time"
                        v-model="form.check_out_time"
                    />
                </div>
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <Label for="status">
                    {{ __('Status') }} <span class="text-destructive">*</span>
                </Label>
                <Select :model-value="form.status" @update:model-value="handleStatusChange">
                    <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                        <SelectValue :placeholder="__('Select status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="(label, value) in props.statuses"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-xs text-destructive">
                    {{ form.errors.status }}
                </p>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <Label for="notes">{{ __('Notes') }}</Label>
                <Textarea
                    id="notes"
                    v-model="form.notes"
                    :placeholder="__('Add any notes...')"
                    rows="3"
                />
            </div>
        </div>
    </ModalForm>
</template>

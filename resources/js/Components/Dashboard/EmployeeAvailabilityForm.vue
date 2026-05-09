<script setup lang="ts">
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
import { useTranslation } from '@/composables/useTranslation';

export interface EmployeeOption {
    id: number;
    full_name: string;
    employee_code: string;
}

export interface EmployeeAvailabilityFormShape {
    employee_id: number | null;
    day_of_week: string;
    start_time: string;
    end_time: string;
    is_active: boolean;
    notes: string;
    errors: Record<string, string>;
}

interface Props {
    form: EmployeeAvailabilityFormShape;
    mode: 'create' | 'edit';
    employees: EmployeeOption[];
    days: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);
</script>

<template>
    <div class="space-y-4">
        <!-- Employee -->
        <div class="space-y-2">
            <Label for="employee_id">
                {{ __('Employee') }} <span class="text-destructive">*</span>
            </Label>
            <Select
                :model-value="props.form.employee_id?.toString() || ''"
                @update:model-value="(v) => (props.form.employee_id = v ? Number(v) : null)"
            >
                <SelectTrigger :class="{ 'border-destructive': props.form.errors.employee_id }">
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
            <p v-if="props.form.errors.employee_id" class="text-xs text-destructive">
                {{ props.form.errors.employee_id }}
            </p>
        </div>

        <!-- Day of week -->
        <div class="space-y-2">
            <Label for="day_of_week">
                {{ __('Day of Week') }} <span class="text-destructive">*</span>
            </Label>
            <Select v-model="props.form.day_of_week">
                <SelectTrigger :class="{ 'border-destructive': props.form.errors.day_of_week }">
                    <SelectValue :placeholder="__('Select day')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="d in props.days" :key="d" :value="d">
                        {{ __(dayLabel(d)) }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p v-if="props.form.errors.day_of_week" class="text-xs text-destructive">
                {{ props.form.errors.day_of_week }}
            </p>
        </div>

        <!-- Time range -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_time">
                    {{ __('Start Time') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="start_time"
                    type="time"
                    v-model="props.form.start_time"
                    :class="{ 'border-destructive': props.form.errors.start_time }"
                />
                <p v-if="props.form.errors.start_time" class="text-xs text-destructive">
                    {{ props.form.errors.start_time }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="end_time">
                    {{ __('End Time') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="end_time"
                    type="time"
                    v-model="props.form.end_time"
                    :class="{ 'border-destructive': props.form.errors.end_time }"
                />
                <p v-if="props.form.errors.end_time" class="text-xs text-destructive">
                    {{ props.form.errors.end_time }}
                </p>
            </div>
        </div>

        <!-- Active -->
        <div class="flex items-center gap-2 pt-2">
            <input
                id="is_active"
                type="checkbox"
                v-model="props.form.is_active"
                class="h-4 w-4"
            />
            <Label for="is_active">{{ __('Active') }}</Label>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
            <Label for="notes">{{ __('Notes') }}</Label>
            <Textarea
                id="notes"
                v-model="props.form.notes"
                :placeholder="__('Optional notes (e.g. \'morning shift, covers reception\')...')"
                rows="3"
                :class="{ 'border-destructive': props.form.errors.notes }"
            />
            <p v-if="props.form.errors.notes" class="text-xs text-destructive">
                {{ props.form.errors.notes }}
            </p>
        </div>
    </div>
</template>

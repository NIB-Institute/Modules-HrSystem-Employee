<script setup lang="ts">
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
import { computed } from 'vue';
import TiptapEditor from '@/components/TiptapEditor.vue';

export interface PlanOption {
    id: number;
    uuid: string;
    title: string;
    start_date?: string | null;
    end_date?: string | null;
    status?: string;
}

export interface EmployeeOption {
    id: number;
    full_name: string;
    employee_code: string;
}

export interface AvailabilityOption {
    id: number;
    employee_id: number;
    day_of_week: string;
    start_time: string | null;
    end_time: string | null;
}

export interface EmployeePlanAssignmentFormShape {
    employee_plan_id: number | null;
    employee_id: number | null;
    employee_availability_id: number | null;
    status: string;
    started_at: string;
    completed_at: string;
    notes: string;
    errors: Record<string, string>;
}

interface Props {
    form: EmployeePlanAssignmentFormShape;
    mode: 'create' | 'edit';
    plans?: PlanOption[];
    employees?: EmployeeOption[];
    availabilities: AvailabilityOption[];
    statuses: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const filteredAvailabilities = computed(() => {
    if (!props.form.employee_id) return [];
    return props.availabilities.filter(a => a.employee_id === props.form.employee_id);
});

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);
</script>

<template>
    <div class="space-y-4">
        <!-- Plan -->
        <div v-if="props.mode === 'create'" class="space-y-2">
            <Label for="employee_plan_id">
                {{ __('Plan') }} <span class="text-destructive">*</span>
            </Label>
            <Select
                :model-value="props.form.employee_plan_id?.toString() || ''"
                @update:model-value="(v) => (props.form.employee_plan_id = v ? Number(v) : null)"
            >
                <SelectTrigger :class="{ 'border-destructive': props.form.errors.employee_plan_id }">
                    <SelectValue :placeholder="__('Select Plan')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="plan in props.plans"
                        :key="plan.id"
                        :value="plan.id.toString()"
                    >
                        {{ plan.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p v-if="props.form.errors.employee_plan_id" class="text-xs text-destructive">
                {{ props.form.errors.employee_plan_id }}
            </p>
        </div>

        <!-- Employee -->
        <div v-if="props.mode === 'create'" class="space-y-2">
            <Label for="employee_id">
                {{ __('Employee') }} <span class="text-destructive">*</span>
            </Label>
            <Select
                :model-value="props.form.employee_id?.toString() || ''"
                @update:model-value="(v) => {
                    props.form.employee_id = v ? Number(v) : null;
                    props.form.employee_availability_id = null;
                }"
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

        <!-- Availability slot (optional) -->
        <div class="space-y-2">
            <Label for="employee_availability_id">
                {{ __('Availability Slot') }}
                <span class="text-xs text-muted-foreground ml-1">({{ __('optional') }})</span>
            </Label>
            <Select
                :model-value="props.form.employee_availability_id?.toString() || ''"
                @update:model-value="(v) => (props.form.employee_availability_id = v && v !== 'none' ? Number(v) : null)"
            >
                <SelectTrigger>
                    <SelectValue :placeholder="props.form.employee_id
                        ? (filteredAvailabilities.length ? __('Choose a slot') : __('No active slots for this employee'))
                        : __('Pick an employee first')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="none">{{ __('— None —') }}</SelectItem>
                    <SelectItem
                        v-for="slot in filteredAvailabilities"
                        :key="slot.id"
                        :value="slot.id.toString()"
                    >
                        {{ __(dayLabel(slot.day_of_week)) }} · {{ slot.start_time }}–{{ slot.end_time }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p v-if="props.form.errors.employee_availability_id" class="text-xs text-destructive">
                {{ props.form.errors.employee_availability_id }}
            </p>
        </div>

        <!-- Status (edit) -->
        <div v-if="props.mode === 'edit'" class="space-y-2">
            <Label for="status">
                {{ __('Status') }} <span class="text-destructive">*</span>
            </Label>
            <Select v-model="props.form.status">
                <SelectTrigger :class="{ 'border-destructive': props.form.errors.status }">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="s in props.statuses" :key="s" :value="s">{{ __(s) }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="props.form.errors.status" class="text-xs text-destructive">
                {{ props.form.errors.status }}
            </p>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
            <Label for="notes">{{ __('Notes') }}</Label>
            <TiptapEditor
                id="notes"
                v-model="props.form.notes"
                :placeholder="__('Optional notes about this assignment...')"
                rows="3"
            />
            <p v-if="props.form.errors.notes" class="text-xs text-destructive">
                {{ props.form.errors.notes }}
            </p>
        </div>
    </div>
</template>

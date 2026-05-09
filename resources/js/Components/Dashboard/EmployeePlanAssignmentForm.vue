<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { SearchableSelect } from '@/components/shared';
import type { SearchableSelectOption } from '@/components/shared';
import { useTranslation } from '@/composables/useTranslation';
import { CheckCircle2, AlertTriangle, CircleSlash } from 'lucide-vue-next';
import { computed } from 'vue';
import TiptapEditor from '@/components/TiptapEditor.vue';

export interface PlanOption {
    id: number;
    uuid: string;
    title: string;
    start_date?: string | null;
    end_date?: string | null;
    start_time?: string | null;
    end_time?: string | null;
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
    is_active?: boolean;
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
    plan?: PlanOption | null; // edit mode hands the resolved plan in
    employees?: EmployeeOption[];
    availabilities: AvailabilityOption[];
    statuses: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const DAY_NAMES = [
    'sunday', 'monday', 'tuesday', 'wednesday',
    'thursday', 'friday', 'saturday',
];

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);

const planOptions = computed<SearchableSelectOption[]>(() =>
    (props.plans ?? []).map(p => ({
        value: p.id,
        label: p.title,
        description: p.start_date && p.end_date ? `${p.start_date} → ${p.end_date}` : undefined,
    })),
);

const employeeOptions = computed<SearchableSelectOption[]>(() =>
    (props.employees ?? []).map(e => ({
        value: e.id,
        label: e.full_name,
        description: e.employee_code,
    })),
);

const filteredAvailabilities = computed(() => {
    if (!props.form.employee_id) return [];
    return props.availabilities.filter(a => a.employee_id === props.form.employee_id);
});

const availabilityOptions = computed<SearchableSelectOption[]>(() =>
    filteredAvailabilities.value.map(a => ({
        value: a.id,
        label: `${__(dayLabel(a.day_of_week))} · ${a.start_time}–${a.end_time}`
            + (a.is_active === false ? ` (${__('inactive')})` : ''),
    })),
);

const statusOptions = computed<SearchableSelectOption[]>(() =>
    props.statuses.map(s => ({
        value: s,
        label: __(s),
    })),
);

const planValue = computed({
    get: () => props.form.employee_plan_id,
    set: (v) => { props.form.employee_plan_id = v == null ? null : Number(v); },
});

const employeeValue = computed({
    get: () => props.form.employee_id,
    set: (v) => {
        props.form.employee_id = v == null ? null : Number(v);
        props.form.employee_availability_id = null;
    },
});

const availabilityValue = computed({
    get: () => props.form.employee_availability_id,
    set: (v) => { props.form.employee_availability_id = v == null ? null : Number(v); },
});

const statusValue = computed({
    get: () => props.form.status,
    set: (v) => { props.form.status = (v as string) ?? ''; },
});

const availabilityPlaceholder = computed(() => {
    if (!props.form.employee_id) return __('Pick an employee first');
    if (!filteredAvailabilities.value.length) return __('No active slots for this employee');
    return __('Choose a slot');
});

// Plan resolved either from the plans list (create mode) or directly (edit mode)
const selectedPlan = computed<PlanOption | null>(() => {
    if (props.plan) return props.plan;
    if (!props.form.employee_plan_id) return null;
    return props.plans?.find(p => p.id === props.form.employee_plan_id) ?? null;
});

const selectedSlot = computed<AvailabilityOption | null>(() => {
    if (!props.form.employee_availability_id) return null;
    return props.availabilities.find(a => a.id === props.form.employee_availability_id) ?? null;
});

const isSelectedSlotInactive = computed(() => selectedSlot.value?.is_active === false);

// Day-of-week set covered by the plan's start_date..end_date.
const planDaySet = computed<Set<string>>(() => {
    const plan = selectedPlan.value;
    const set = new Set<string>();
    if (!plan?.start_date || !plan?.end_date) return set;

    const start = new Date(plan.start_date + 'T00:00:00');
    const end = new Date(plan.end_date + 'T00:00:00');
    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return set;

    const diffDays = Math.floor((end.getTime() - start.getTime()) / 86_400_000);
    if (diffDays >= 6) {
        DAY_NAMES.forEach(d => set.add(d));
        return set;
    }
    for (const d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        set.add(DAY_NAMES[d.getDay()]);
    }
    return set;
});

interface FitResult {
    level: 'fit' | 'warn';
    text: string;
}

const fitHint = computed<FitResult | null>(() => {
    const plan = selectedPlan.value;
    const slot = selectedSlot.value;
    if (!plan || !slot) return null;

    const daySet = planDaySet.value;
    if (daySet.size && !daySet.has(slot.day_of_week)) {
        return {
            level: 'warn',
            text: __('Slot day-of-week is outside this plan\'s date range.'),
        };
    }

    const planStart = plan.start_time;
    const planEnd = plan.end_time;
    if (planStart && planEnd && slot.start_time && slot.end_time) {
        if (slot.start_time > planStart || slot.end_time < planEnd) {
            return {
                level: 'warn',
                text: __('Slot time window does not cover the plan\'s scheduled time.'),
            };
        }
    }

    return {
        level: 'fit',
        text: __('Slot fits the plan\'s date range and time.'),
    };
});
</script>

<template>
    <div class="space-y-4">
        <!-- Plan + Employee (create mode) -->
        <div v-if="props.mode === 'create'" class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="employee_plan_id">
                    {{ __('Plan') }} <span class="text-destructive">*</span>
                </Label>
                <SearchableSelect
                    v-model="planValue"
                    :options="planOptions"
                    :placeholder="__('Select a plan')"
                    :search-placeholder="__('Search plans...')"
                    :empty-message="__('No plans found.')"
                />
                <p v-if="props.form.errors.employee_plan_id" class="text-xs text-destructive">
                    {{ props.form.errors.employee_plan_id }}
                </p>
            </div>

            <div class="space-y-2">
                <Label for="employee_id">
                    {{ __('Employee') }} <span class="text-destructive">*</span>
                </Label>
                <SearchableSelect
                    v-model="employeeValue"
                    :options="employeeOptions"
                    :placeholder="__('Select an employee')"
                    :search-placeholder="__('Search employees...')"
                    :empty-message="__('No employees found.')"
                />
                <p v-if="props.form.errors.employee_id" class="text-xs text-destructive">
                    {{ props.form.errors.employee_id }}
                </p>
            </div>
        </div>

        <!-- Availability + Status -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="employee_availability_id">
                    {{ __('Availability Slot') }}
                    <span class="text-xs text-muted-foreground ml-1">({{ __('optional') }})</span>
                </Label>
                <SearchableSelect
                    v-model="availabilityValue"
                    :options="availabilityOptions"
                    :placeholder="availabilityPlaceholder"
                    :search-placeholder="__('Search slots...')"
                    :empty-message="__('No slots match.')"
                    :disabled="!props.form.employee_id || availabilityOptions.length === 0"
                />

                <!-- Fit hint -->
                <div v-if="fitHint" class="flex items-start gap-2 text-xs">
                    <CheckCircle2
                        v-if="fitHint.level === 'fit'"
                        class="mt-0.5 h-4 w-4 shrink-0 text-green-600"
                    />
                    <AlertTriangle
                        v-else
                        class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                    />
                    <span :class="fitHint.level === 'fit' ? 'text-green-700' : 'text-amber-700'">
                        {{ fitHint.text }}
                    </span>
                </div>

                <!-- Inactive slot badge -->
                <div v-if="isSelectedSlotInactive" class="flex items-center gap-1.5">
                    <Badge variant="secondary" class="text-xs">
                        <CircleSlash class="mr-1 h-3 w-3" />
                        {{ __('This slot is currently inactive') }}
                    </Badge>
                </div>

                <p v-if="props.form.errors.employee_availability_id" class="text-xs text-destructive">
                    {{ props.form.errors.employee_availability_id }}
                </p>
            </div>

            <div v-if="props.mode === 'edit'" class="space-y-2">
                <Label for="status">
                    {{ __('Status') }} <span class="text-destructive">*</span>
                </Label>
                <SearchableSelect
                    v-model="statusValue"
                    :options="statusOptions"
                    :placeholder="__('Select status')"
                    :search-placeholder="__('Search status...')"
                />
                <p v-if="props.form.errors.status" class="text-xs text-destructive">
                    {{ props.form.errors.status }}
                </p>
            </div>
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

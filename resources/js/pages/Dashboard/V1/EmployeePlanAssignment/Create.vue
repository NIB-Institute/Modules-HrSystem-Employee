<script setup lang="ts">
import { ModalForm, SearchableSelect } from '@/components/shared';
import type { SearchableSelectOption } from '@/components/shared';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { useTranslation } from '@/composables/useTranslation';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { X, Users } from 'lucide-vue-next';

interface PlanOption {
    id: number;
    uuid: string;
    title: string;
    start_date?: string | null;
    end_date?: string | null;
}

interface EmployeeOption {
    id: number;
    full_name: string;
    employee_code: string;
}

interface Props {
    plans: PlanOption[];
    employees: EmployeeOption[];
    existingAssignments?: Record<number, number[]>;
    selectedPlanId?: number | null;
}

const props = defineProps<Props>();

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
    employee_plan_id: props.selectedPlanId ?? (null as number | null),
    employee_ids: [] as number[],
    notes: '',
});

const employeeSearch = ref('');

const planOptions = computed<SearchableSelectOption[]>(() =>
    props.plans.map(p => ({
        value: p.id,
        label: p.title,
        description: p.start_date && p.end_date ? `${p.start_date} → ${p.end_date}` : undefined,
    })),
);

const planValue = computed({
    get: () => form.employee_plan_id,
    set: (v) => { form.employee_plan_id = v == null ? null : Number(v); },
});

// Employees already on the currently-selected plan — these get disabled in the
// checklist so the user can't accidentally pick them and get "0 created".
const alreadyAssignedIds = computed<Set<number>>(() => {
    const planId = form.employee_plan_id;
    if (!planId || !props.existingAssignments) return new Set<number>();
    return new Set(props.existingAssignments[planId] ?? []);
});

const isAlreadyAssigned = (id: number) => alreadyAssignedIds.value.has(id);

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase();
    if (!q) return props.employees;
    return props.employees.filter(e =>
        e.full_name.toLowerCase().includes(q)
        || (e.employee_code ?? '').toLowerCase().includes(q),
    );
});

// Employees you can still pick (not already on the plan)
const selectableEmployees = computed(() =>
    filteredEmployees.value.filter(e => !isAlreadyAssigned(e.id)),
);

const isSelected = (id: number) => form.employee_ids.includes(id);

const toggleEmployee = (id: number) => {
    if (isAlreadyAssigned(id)) return; // guard
    if (isSelected(id)) {
        form.employee_ids = form.employee_ids.filter(x => x !== id);
    } else {
        form.employee_ids = [...form.employee_ids, id];
    }
};

const removeEmployee = (id: number) => {
    form.employee_ids = form.employee_ids.filter(x => x !== id);
};

const selectedEmployees = computed(() =>
    props.employees.filter(e => form.employee_ids.includes(e.id)),
);

const selectAll = () => {
    form.employee_ids = selectableEmployees.value.map(e => e.id);
};

const clearAll = () => {
    form.employee_ids = [];
};

// When the user switches plans, drop any previously-checked employees that are
// already on the newly-selected plan.
watch(() => form.employee_plan_id, () => {
    form.employee_ids = form.employee_ids.filter(id => !isAlreadyAssigned(id));
});

const isFormInvalid = computed(() => !form.employee_plan_id || form.employee_ids.length === 0);

const handleSubmit = () => {
    form.post('/dashboard/employee-plan-assignments/bulk-assign', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__(`Assigned ${form.employee_ids.length} employee(s) to the plan.`));
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
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Assign Employees to Plan')"
        :description="__('Pick a plan, then choose one or many employees to assign at once.')"
        mode="create"
        size="lg"
        :submit-text="__('Assign')"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-5">
            <!-- Plan -->
            <div class="space-y-2">
                <Label>{{ __('Plan') }} <span class="text-destructive">*</span></Label>
                <SearchableSelect
                    v-model="planValue"
                    :options="planOptions"
                    :placeholder="__('Select a plan')"
                    :search-placeholder="__('Search plans...')"
                />
                <p v-if="form.errors.employee_plan_id" class="text-xs text-destructive">
                    {{ form.errors.employee_plan_id }}
                </p>
            </div>

            <!-- Employees (multi-select) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label>
                        <Users class="inline h-3.5 w-3.5 mr-1" />
                        {{ __('Employees') }} <span class="text-destructive">*</span>
                        <span v-if="form.employee_ids.length > 0" class="ml-2 text-xs text-muted-foreground">
                            ({{ form.employee_ids.length }} {{ __('selected') }})
                        </span>
                    </Label>
                    <div class="flex gap-2 text-xs">
                        <button type="button" @click="selectAll" class="text-primary hover:underline">
                            {{ __('Select all') }}
                        </button>
                        <span class="text-muted-foreground">·</span>
                        <button type="button" @click="clearAll" class="text-muted-foreground hover:underline">
                            {{ __('Clear') }}
                        </button>
                    </div>
                </div>

                <!-- Selected chips -->
                <div v-if="selectedEmployees.length > 0" class="flex flex-wrap gap-1.5 rounded-lg border bg-muted/30 p-2">
                    <Badge v-for="e in selectedEmployees" :key="e.id" variant="secondary" class="gap-1 pr-1">
                        {{ e.full_name }}
                        <button type="button" @click="removeEmployee(e.id)" class="rounded hover:bg-muted-foreground/20 p-0.5">
                            <X class="h-3 w-3" />
                        </button>
                    </Badge>
                </div>

                <!-- Search + checklist -->
                <div class="rounded-lg border">
                    <div class="border-b p-2">
                        <Input
                            v-model="employeeSearch"
                            :placeholder="__('Search employees by name or code...')"
                            class="h-8"
                        />
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <div v-if="filteredEmployees.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                            {{ __('No employees found.') }}
                        </div>
                        <label
                            v-for="e in filteredEmployees"
                            :key="e.id"
                            :class="[
                                'flex items-center gap-3 p-2.5 border-b last:border-b-0',
                                isAlreadyAssigned(e.id)
                                    ? 'opacity-50 cursor-not-allowed bg-muted/30'
                                    : 'hover:bg-muted/50 cursor-pointer',
                            ]"
                        >
                            <input
                                type="checkbox"
                                :checked="isSelected(e.id)"
                                :disabled="isAlreadyAssigned(e.id)"
                                @change="toggleEmployee(e.id)"
                                class="h-4 w-4 rounded border-input disabled:cursor-not-allowed"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate flex items-center gap-2">
                                    {{ e.full_name }}
                                    <Badge v-if="isAlreadyAssigned(e.id)" variant="outline" class="text-[10px] py-0 px-1.5 h-4">
                                        {{ __('already assigned') }}
                                    </Badge>
                                </div>
                                <div class="text-xs text-muted-foreground truncate">{{ e.employee_code }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <p v-if="form.errors.employee_ids" class="text-xs text-destructive">
                    {{ form.errors.employee_ids }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ __('Already-assigned employees are skipped automatically (no duplicates).') }}
                </p>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <Label>{{ __('Notes') }}</Label>
                <TiptapEditor
                    v-model="form.notes"
                    :placeholder="__('Optional notes that apply to every assignment')"
                    min-height="80px"
                    max-height="180px"
                />
                <p v-if="form.errors.notes" class="text-xs text-destructive">
                    {{ form.errors.notes }}
                </p>
            </div>
        </div>
    </ModalForm>
</template>

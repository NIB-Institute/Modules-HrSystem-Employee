<script setup lang="ts">
import { computed } from 'vue';
import { useTranslation } from '@/composables/useTranslation';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Building2, Briefcase, DollarSign, Calendar, Award, FileText, Hash } from 'lucide-vue-next';
import type { InertiaForm } from '@inertiajs/vue3';
import type { EmployeeFormData, SchoolOption, DepartmentOption, EmployeeTypeOption } from '../../../../types';

interface Props {
    form: InertiaForm<EmployeeFormData>;
    schools?: SchoolOption[];
    departments?: DepartmentOption[];
    employeeTypes?: EmployeeTypeOption[];
}

const props = withDefaults(defineProps<Props>(), {
    schools: () => [],
    departments: () => [],
    employeeTypes: () => [],
});

const emit = defineEmits<{
    schoolChange: [schoolId: number | null];
}>();

const { __ } = useTranslation();

const selectedSchool = computed({
    get: () => props.form.school_id?.toString(),
    set: (value: string | undefined) => {
        const newValue = value ? parseInt(value) : null;
        props.form.school_id = newValue;
        props.form.department_id = null;
        emit('schoolChange', newValue);
    },
});

const selectedDepartment = computed({
    get: () => props.form.department_id?.toString(),
    set: (value: string | undefined) => {
        props.form.department_id = value ? parseInt(value) : null;
    },
});

const selectedEmployeeType = computed({
    get: () => props.form.employee_type || undefined,
    set: (value: string | undefined) => {
        props.form.employee_type = (value as 'full_time' | 'part_time' | 'contract' | 'intern') || null;
    },
});

const salaryValue = computed({
    get: () => props.form.salary ?? undefined,
    set: (value: number | undefined) => {
        props.form.salary = value ?? null;
    },
});
</script>

<template>
    <Card>
        <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-2 text-base">
                <Briefcase class="h-4 w-4 text-primary" />
                {{ __('Employment Information') }}
            </CardTitle>
            <CardDescription>{{ __('Work assignment and compensation details') }}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Organization Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Building2 class="h-4 w-4" />
                    <span>{{ __('Organization') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 rounded-lg border bg-muted/30 p-4">
                    <!-- School -->
                    <div class="space-y-2">
                        <Label for="school_id" class="text-xs font-medium">{{ __('School') }}</Label>
                        <Select v-model="selectedSchool">
                            <SelectTrigger id="school_id" class="bg-background">
                                <SelectValue :placeholder="__('Select school')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="school in schools" :key="school.id" :value="school.id.toString()">
                                    {{ school.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.school_id" class="text-xs text-destructive">{{ form.errors.school_id }}</p>
                    </div>

                    <!-- Department -->
                    <div class="space-y-2">
                        <Label for="department_id" class="text-xs font-medium">{{ __('Department') }}</Label>
                        <Select v-model="selectedDepartment" :disabled="!form.school_id">
                            <SelectTrigger id="department_id" class="bg-background">
                                <SelectValue :placeholder="__('Select department')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()">
                                    {{ dept.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.department_id" class="text-xs text-destructive">{{ form.errors.department_id }}</p>
                        <p v-if="!form.school_id" class="text-xs text-muted-foreground">{{ __('Select a school first') }}</p>
                    </div>

                    <!-- Job Title -->
                    <div class="space-y-2">
                        <Label for="job_title" class="text-xs font-medium">{{ __('Job Title') }}</Label>
                        <Input id="job_title" v-model="form.job_title" type="text" placeholder="e.g. Software Engineer" class="bg-background" />
                        <p v-if="form.errors.job_title" class="text-xs text-destructive">{{ form.errors.job_title }}</p>
                    </div>
                </div>
            </div>

            <!-- Compensation Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <DollarSign class="h-4 w-4" />
                    <span>{{ __('Compensation & Type') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 rounded-lg border bg-muted/30 p-4">
                    <!-- Employee Type -->
                    <div class="space-y-2">
                        <Label for="employee_type" class="text-xs font-medium">{{ __('Employee Type') }}</Label>
                        <Select v-model="selectedEmployeeType">
                            <SelectTrigger id="employee_type" class="bg-background">
                                <SelectValue :placeholder="__('Select type')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="type in employeeTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.employee_type" class="text-xs text-destructive">{{ form.errors.employee_type }}</p>
                    </div>

                    <!-- Salary -->
                    <div class="space-y-2">
                        <Label for="salary" class="text-xs font-medium">{{ __('Monthly Salary') }}</Label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">$</span>
                            <Input id="salary" v-model.number="salaryValue" type="number" min="0" step="0.01" placeholder="0.00" class="pl-7 bg-background" />
                        </div>
                        <p v-if="form.errors.salary" class="text-xs text-destructive">{{ form.errors.salary }}</p>
                    </div>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Calendar class="h-4 w-4" />
                    <span>{{ __('Employment Timeline') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-3 rounded-lg border bg-muted/30 p-4">
                    <!-- Hire Date -->
                    <div class="space-y-2">
                        <Label for="hire_date" class="text-xs font-medium">{{ __('Hire Date') }}</Label>
                        <Input id="hire_date" v-model="form.hire_date" type="date" class="bg-background" />
                        <p v-if="form.errors.hire_date" class="text-xs text-destructive">{{ form.errors.hire_date }}</p>
                    </div>

                    <!-- Probation Start -->
                    <div class="space-y-2">
                        <Label for="probation_date" class="text-xs font-medium">{{ __('Probation Start') }}</Label>
                        <Input id="probation_date" v-model="form.probation_date" type="date" class="bg-background" />
                        <p v-if="form.errors.probation_date" class="text-xs text-destructive">{{ form.errors.probation_date }}</p>
                    </div>

                    <!-- Probation End -->
                    <div class="space-y-2">
                        <Label for="probation_end_date" class="text-xs font-medium">{{ __('Probation End') }}</Label>
                        <Input id="probation_end_date" v-model="form.probation_end_date" type="date" class="bg-background" />
                        <p v-if="form.errors.probation_end_date" class="text-xs text-destructive">{{ form.errors.probation_end_date }}</p>
                    </div>
                </div>
            </div>

            <!-- Certification Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Award class="h-4 w-4" />
                    <span>{{ __('Certification') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 rounded-lg border bg-muted/30 p-4">
                    <div class="space-y-2">
                        <Label for="certificate" class="text-xs font-medium">{{ __('Certificate Name') }}</Label>
                        <div class="relative">
                            <FileText class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input id="certificate" v-model="form.certificate" type="text" placeholder="Bachelor's Degree" class="pl-10 bg-background" />
                        </div>
                        <p v-if="form.errors.certificate" class="text-xs text-destructive">{{ form.errors.certificate }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="certificate_code" class="text-xs font-medium">{{ __('Certificate Code') }}</Label>
                        <div class="relative">
                            <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input id="certificate_code" v-model="form.certificate_code" type="text" placeholder="CERT-2024-001" class="pl-10 bg-background" />
                        </div>
                        <p v-if="form.errors.certificate_code" class="text-xs text-destructive">{{ form.errors.certificate_code }}</p>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

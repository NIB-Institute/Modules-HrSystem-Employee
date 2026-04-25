<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import EmployeeForm from '@employee/Components/Dashboard/EmployeeForm.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { employeeSchema } from '@employee/validation/employeeSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import { ChevronLeft } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { EmployeeFormData, EmployeeEditProps, DepartmentOption } from '@employee/types';

const props = defineProps<EmployeeEditProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Employees', href: '/dashboard/employees' },
    { title: props.employee.full_name, href: `/dashboard/employees/${props.employee.uuid}` },
    { title: 'Edit', href: `/dashboard/employees/${props.employee.uuid}/edit` },
];

const departments = ref<DepartmentOption[]>(props.departments || []);

// Initialize family members with _key for Vue tracking
const initializeFamilyMembers = () => {
    if (!props.employee.family_members) return [];
    return props.employee.family_members.map((member, index) => ({
        ...member,
        _key: index + 1,
        date_of_birth: member.date_of_birth || '',
        occupation: member.occupation || '',
        phone_number: member.phone_number || '',
        email: member.email || '',
        address: member.address || '',
        notes: member.notes || '',
    }));
};

// Initialize academic levels with _key for Vue tracking
const initializeAcademicLevels = () => {
    if (!props.employee.academic_levels) return [];
    return props.employee.academic_levels.map((level, index) => ({
        ...level,
        _key: index + 1,
        field_of_study: level.field_of_study || '',
        degree: level.degree || '',
        start_date: level.start_date || '',
        end_date: level.end_date || '',
        certificate: level.certificate || '',
        notes: level.notes || '',
    }));
};

// Initialize foreign languages with _key for Vue tracking
const initializeForeignLanguages = () => {
    if (!props.employee.foreign_languages) return [];
    return props.employee.foreign_languages.map((lang, index) => ({
        ...lang,
        _key: index + 1,
        certificate: lang.certificate || '',
        certificate_score: lang.certificate_score || '',
        notes: lang.notes || '',
    }));
};

// Initialize job experiences with _key for Vue tracking
const initializeJobExperiences = () => {
    if (!props.employee.job_experiences) return [];
    return props.employee.job_experiences.map((exp, index) => ({
        ...exp,
        _key: index + 1,
        position: exp.position || '',
        province: exp.province || '',
        city: exp.city || '',
        start_date: exp.start_date || '',
        end_date: exp.end_date || '',
        responsibilities: exp.responsibilities || '',
        achievements: exp.achievements || '',
        reason_for_leaving: exp.reason_for_leaving || '',
        notes: exp.notes || '',
    }));
};

const form = useForm<EmployeeFormData>({
    employee_code: props.employee.employee_code,
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email || '',
    phone_number: props.employee.phone_number || '',
    gender: props.employee.gender,
    marital_status: props.employee.marital_status,
    date_of_birth: props.employee.date_of_birth || '',
    birth_place: props.employee.birth_place || '',
    ethnicity: props.employee.ethnicity || '',
    current_address: props.employee.current_address || '',
    school_id: props.employee.school_id,
    department_id: props.employee.department_id,
    position_id: props.employee.position_id,
    type_employee_id: props.employee.type_employee_id,
    job_title: props.employee.job_title || '',
    employee_type: props.employee.employee_type,
    salary: props.employee.salary,
    hire_date: props.employee.hire_date || '',
    probation_date: props.employee.probation_date || '',
    probation_end_date: props.employee.probation_end_date || '',
    certificate: props.employee.certificate || '',
    certificate_image: props.employee.certificate_image || '',
    certificate_code: props.employee.certificate_code || '',
    avatar_url: props.employee.avatar_url || '',
    status: props.employee.status,
    certificate_images: props.employee.certificate_images || [],
    family_members: initializeFamilyMembers(),
    academic_levels: initializeAcademicLevels(),
    foreign_languages: initializeForeignLanguages(),
    job_experiences: initializeJobExperiences(),
});

const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    employeeSchema,
    ['employee_code', 'first_name', 'last_name']
);

const getFormData = () => ({
    employee_code: form.employee_code,
    first_name: form.first_name,
    last_name: form.last_name,
    email: form.email || null,
    phone_number: form.phone_number || null,
    gender: form.gender,
    marital_status: form.marital_status,
    date_of_birth: form.date_of_birth || null,
    birth_place: form.birth_place || null,
    ethnicity: form.ethnicity || null,
    current_address: form.current_address || null,
    school_id: form.school_id,
    department_id: form.department_id,
    position_id: form.position_id,
    job_title: form.job_title || null,
    employee_type: form.employee_type,
    salary: form.salary,
    hire_date: form.hire_date || null,
    probation_date: form.probation_date || null,
    probation_end_date: form.probation_end_date || null,
    certificate: form.certificate || null,
    certificate_image: form.certificate_image || null,
    certificate_code: form.certificate_code || null,
    avatar_url: form.avatar_url || null,
    status: form.status,
    certificate_images: form.certificate_images,
    family_members: form.family_members,
    academic_levels: form.academic_levels,
    foreign_languages: form.foreign_languages,
    job_experiences: form.job_experiences,
});

watch([() => form.first_name, () => form.last_name], () => validateForm(getFormData()));

const isFormInvalid = createIsFormInvalid(getFormData);

const handleSchoolChange = async (schoolId: number | null) => {
    if (!schoolId) {
        departments.value = [];
        return;
    }

    try {
        const response = await fetch(`/dashboard/employees/departments?school_id=${schoolId}`);
        if (response.ok) {
            departments.value = await response.json();
        }
    } catch (error) {
        departments.value = [];
    }
};

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.put(`/dashboard/employees/${props.employee.uuid}`, {
            onSuccess: () => {
                toast.success('Employee updated successfully.');
                router.visit(`/dashboard/employees/${props.employee.uuid}`);
            },
        });
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${employee.full_name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
               <Link href="/dashboard/employees" class="text-muted-foreground hover:text-foreground">
                    <ChevronLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold">Edit Employee</h1>
                    <p class="text-sm text-muted-foreground">{{ employee.full_name }} - {{ employee.employee_code }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <EmployeeForm
                    :form="form"
                    mode="edit"
                    :schools="props.schools"
                    :departments="departments"
                    :employee-types="props.employeeTypes"
                    :marital-statuses="props.maritalStatuses"
                    :relationship-types="props.relationshipTypes"
                    :academic-levels="props.academicLevels"
                    :language-proficiencies="props.languageProficiencies"
                    :employment-types="props.employmentTypes"
                    @school-change="handleSchoolChange"
                />

                <!-- Actions at Bottom -->
                <div class="flex justify-end gap-3 pt-4">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="`/dashboard/employees/${employee.uuid}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="isFormInvalid || form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

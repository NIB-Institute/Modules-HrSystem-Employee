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
import type { BreadcrumbItem } from '@/types';
import type { EmployeeFormData, EmployeeCreateProps, DepartmentOption } from '@employee/types';
import { ChevronLeft } from 'lucide-vue-next';

const props = defineProps<EmployeeCreateProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Employees', href: '/dashboard/employees' },
    { title: 'Create', href: '/dashboard/employees/create' },
];

const departments = ref<DepartmentOption[]>(props.departments || []);

const form = useForm<EmployeeFormData>({
    employee_code: props.generatedCode || '',
    first_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    gender: null,
    marital_status: null,
    date_of_birth: '',
    birth_place: '',
    ethnicity: '',
    current_address: '',
    school_id: null,
    department_id: null,
    position_id: null,
    type_employee_id: null,
    job_title: '',
    employee_type: null,
    salary: null,
    hire_date: '',
    probation_date: '',
    probation_end_date: '',
    certificate: '',
    certificate_image: '',
    certificate_images: [],
    certificate_code: '',
    avatar_url: '',
    status: true,
    // Family members
    family_members: [],
    // Academic levels
    academic_levels: [],
    // Foreign languages
    foreign_languages: [],
    // Job experiences
    job_experiences: [],
    // Account fields
    create_account: false,
    password: '',
    password_confirmation: '',
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
    type_employee_id: form.type_employee_id,
    job_title: form.job_title || null,
    employee_type: form.employee_type,
    salary: form.salary,
    hire_date: form.hire_date || null,
    probation_date: form.probation_date || null,
    probation_end_date: form.probation_end_date || null,
    certificate: form.certificate || null,
    certificate_image: form.certificate_image || null,
    certificate_images: form.certificate_images,
    certificate_code: form.certificate_code || null,
    avatar_url: form.avatar_url || null,
    status: form.status,
    // Family members
    family_members: form.family_members,
    // Academic levels
    academic_levels: form.academic_levels,
    // Foreign languages
    foreign_languages: form.foreign_languages,
    // Job experiences
    job_experiences: form.job_experiences,
    // Account fields
    create_account: form.create_account,
    password: form.password || null,
    password_confirmation: form.password_confirmation || null,
});

watch([() => form.employee_code, () => form.first_name, () => form.last_name], () => {
    if (form.employee_code && form.first_name && form.last_name) {
        validateForm(getFormData());
    }
});

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
        console.error('Failed to fetch departments:', error);
        departments.value = [];
    }
};

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/employees', {
            onSuccess: () => {
                toast.success('Employee created successfully.');
                router.visit('/dashboard/employees');
            },
        });
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Employee" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/dashboard/employees" class="text-muted-foreground hover:text-foreground">
                    <ChevronLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold">Create Employee</h1>
                    <p class="text-sm text-muted-foreground">Add a new employee to your organization</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <EmployeeForm
                    :form="form"
                    mode="create"
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
                        <Link href="/dashboard/employees">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="isFormInvalid || form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Employee' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ImportPage, { type TableColumn } from '@/components/shared/ImportPage.vue';
import type { BreadcrumbItem } from '@/types';
import type { DuplicateOption } from '@/composables/useImport';

defineProps<{
    duplicateOptions: DuplicateOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Employees', href: '/dashboard/employees' },
    { title: 'Import', href: '/dashboard/employees/import' },
];

const previewColumns: TableColumn[] = [
    { key: 'first_name', label: 'First Name', minWidth: '120px' },
    { key: 'last_name', label: 'Last Name', minWidth: '120px' },
    { key: 'email', label: 'Email', minWidth: '180px' },
    { key: 'phone_number', label: 'Phone', minWidth: '120px' },
    { key: 'school', label: 'School', minWidth: '150px' },
    { key: 'department', label: 'Department', minWidth: '150px' },
    { key: 'job_title', label: 'Job Title', minWidth: '120px' },
];

const availableColumns = [
    'First Name *',
    'Last Name *',
    'Email',
    'Phone Number',
    'Gender',
    'Date of Birth',
    'Birth Place',
    'Current Address',
    'School',
    'Department',
    'Employee Type Name',
    'Job Title',
    'Employment Type',
    'Salary',
    'Hire Date',
    'Probation Date',
    'Probation End Date',
    'Status',
];
</script>

<template>
    <ImportPage
        title="Import Employees"
        entity-name="employee"
        entity-name-plural="Employees"
        :breadcrumbs="breadcrumbs"
        :duplicate-options="duplicateOptions"
        preview-url="/dashboard/employees/import/preview"
        import-url="/dashboard/employees/import"
        list-url="/dashboard/employees"
        template-url="/dashboard/employees/template"
        required-fields-text="First Name and Last Name are required. Email must be unique. School and Department must match existing names exactly."
        :available-columns="availableColumns"
        :preview-columns="previewColumns"
    />
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard, ButtonGroup } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Plus, Users, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Clock, CalendarDays, Download, Upload, Database, FileSpreadsheet, Key, UserPlus } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { EmployeeIndexProps, Employee } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const { __ } = useTranslation();

const props = defineProps<EmployeeIndexProps>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employees'), href: '/dashboard/employees' },
]);

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const employeeTypeFilter = ref(props.filters.employee_type || 'all');
const schoolFilter = ref(props.filters.school_id || 'all');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Selection state
const selectedUuids = ref<(string | number)[]>([]);

// Bulk delete handler
const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/employees/bulk-delete?${params.toString()}`);
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const columns = computed<TableColumn<Employee>[]>(() => [
    {
        key: 'employee',
        label: __('Employee'),
        render: (employee) => employee.full_name,
    },
    {
        key: 'employee_code',
        label: __('Code'),
        render: (employee) => employee.employee_code,
    },
    {
        key: 'email',
        label: __('Email'),
        render: (employee) => employee.email || '-',
    },
    {
        key: 'phone_number',
        label: __('Phone'),
        render: (employee) => employee.phone_number || '-',
    },
    {
        key: 'employee_type_label',
        label: __('Type'),
        render: (employee) => employee.employee_type_label || '-',
    },
    {
        key: 'department_name',
        label: __('Department'),
        render: (employee) => employee.department_name || '-',
    },
    {
        key: 'status',
        label: __('Status'),
        render: (employee) => employee.status ? __('Active') : __('Inactive'),
    },
]);

const actions = computed<TableAction<Employee>[]>(() => [
    {
        label: __('View'),
        icon: Eye,
        onClick: (employee) => router.visit(`/dashboard/employees/${employee.uuid}`),
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (employee) => router.visit(`/dashboard/employees/${employee.uuid}/edit`),
    },
    {
        label: __('Change Password'),
        icon: Key,
        onClick: (employee) => router.visit(`/dashboard/employees/${employee.uuid}/change-password`),
        show: (employee) => employee.has_account,
        separator: true,
    },
    {
        label: __('Create Account'),
        icon: UserPlus,
        onClick: (employee) => router.visit(`/dashboard/employees/${employee.uuid}/create-account`),
        show: (employee) => !employee.has_account && (!!employee.email || !!employee.phone_number),
        separator: true,
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (employee) => router.visit(`/dashboard/employees/${employee.uuid}/delete`),
        variant: 'destructive',
    },
]);

const pagination = computed<PaginationData>(() => ({
    current_page: props.employees.meta.current_page,
    last_page: props.employees.meta.last_page,
    per_page: props.employees.meta.per_page,
    total: props.employees.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    employee_type: employeeTypeFilter.value !== 'all' ? employeeTypeFilter.value : undefined,
    school_id: schoolFilter.value !== 'all' ? schoolFilter.value : undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/employees', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/employees', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/employees', getFilterParams(), { preserveState: true });
};

watch([statusFilter, employeeTypeFilter, schoolFilter, dateFrom, dateTo], () => {
    router.get('/dashboard/employees', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/employees/create');
};

const handleExport = () => {
    const params = new URLSearchParams();
    const filters = getFilterParams();
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) {
            params.append(key, String(value));
        }
    });
    window.location.href = `/dashboard/employees/export?${params.toString()}`;
};

const handleImport = () => {
    router.visit('/dashboard/employees/import');
};

const handleDownloadTemplate = () => {
    window.location.href = '/dashboard/employees/template';
};

const handleTrash = () => {
    router.visit('/dashboard/employees/trash');
};

const handleStatusToggle = (employee: Employee, newStatus: boolean) => {
    router.put(`/dashboard/employees/${employee.uuid}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Employees')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Employee Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    :title="__('Total Employees')"
                    :value="props.stats.total"
                    :icon="Users"
                />
                <StatsCard
                    :title="__('Active')"
                    :value="props.stats.active"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    :title="__('Inactive')"
                    :value="props.stats.inactive"
                    :icon="XCircle"
                    variant="warning"
                />
            </div>

            <!-- Attendance Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard
                    :title="__('Present')"
                    :value="props.attendanceStats.present"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    :title="__('Absent')"
                    :value="props.attendanceStats.absent"
                    :icon="XCircle"
                    variant="destructive"
                />
                <StatsCard
                    :title="__('Late')"
                    :value="props.attendanceStats.late"
                    :icon="Clock"
                    variant="warning"
                />
                <StatsCard
                    :title="__('On Leave')"
                    :value="props.attendanceStats.on_leave"
                    :icon="CalendarDays"
                />
            </div>

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Employees') }}</h2>
                        <p class="text-sm text-muted-foreground">{{ __('Manage your employees') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <ButtonGroup>
                            <Button variant="default">
                                <Database class="mr-2 h-4 w-4" />
                                {{ __('All') }}
                            </Button>
                            <Button variant="outline" @click="handleTrash">
                                <Trash2 class="mr-2 h-4 w-4" />
                                {{ __('Trash') }}
                            </Button>
                        </ButtonGroup>
                        <ButtonGroup>
                            <Button variant="outline" @click="handleExport">
                                <Download class="mr-2 h-4 w-4" />
                                {{ __('Export') }}
                            </Button>
                            <Button variant="outline" @click="handleImport">
                                <Upload class="mr-2 h-4 w-4" />
                                {{ __('Import') }}
                            </Button>
                            <Button variant="outline" @click="handleDownloadTemplate">
                                <FileSpreadsheet class="mr-2 h-4 w-4" />
                                {{ __('Template') }}
                            </Button>
                        </ButtonGroup>
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ __('Add Employee') }}
                        </Button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search employees...')"
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue :placeholder="__('Status')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Status') }}</SelectItem>
                            <SelectItem value="1">{{ __('Active') }}</SelectItem>
                            <SelectItem value="0">{{ __('Inactive') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="employeeTypeFilter">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue :placeholder="__('Type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Types') }}</SelectItem>
                            <SelectItem value="full_time">{{ __('Full Time') }}</SelectItem>
                            <SelectItem value="part_time">{{ __('Part Time') }}</SelectItem>
                            <SelectItem value="contract">{{ __('Contract') }}</SelectItem>
                            <SelectItem value="intern">{{ __('Intern') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="schoolFilter">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="__('School')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Schools') }}</SelectItem>
                            <SelectItem
                                v-for="school in props.schools"
                                :key="school.id"
                                :value="school.id.toString()"
                            >
                                {{ school.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="dateFrom"
                            type="date"
                            class="w-[150px]"
                            :placeholder="__('From')"
                        />
                        <span class="text-muted-foreground">{{ __('to') }}</span>
                        <Input
                            v-model="dateTo"
                            type="date"
                            class="w-[150px]"
                            :placeholder="__('To')"
                        />
                    </div>
                </div>

                <!-- Table -->
                <TableReusable
                    v-model:selected="selectedUuids"
                    :data="props.employees.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    :selectable="true"
                    select-key="uuid"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #bulk-actions>
                        <Button variant="destructive" size="sm" @click="openBulkDeleteDialog">
                            <Trash2 class="mr-2 h-4 w-4" />
                            {{ __('Delete Selected') }}
                        </Button>
                    </template>
                    <template #cell-employee="{ item }">
                        <div class="flex items-center gap-3">
                            <Avatar class="h-9 w-9">
                                <AvatarImage :src="item.avatar_url || ''" :alt="item.full_name" class="object-cover" />
                                <AvatarFallback>{{ getInitials(item.full_name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-medium">{{ item.full_name }}</p>
                                <p class="text-sm text-muted-foreground">{{ item.job_title || '-' }}</p>
                            </div>
                        </div>
                    </template>
                    <template #cell-employee_type_label="{ item }">
                        <Badge v-if="item.employee_type_label" variant="outline">
                            {{ item.employee_type_label }}
                        </Badge>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>
                    <template #cell-attendance="{ item }">
                        <div class="flex items-center gap-2">
                            <Badge variant="outline" class="text-green-600">
                                {{ item.attendance_present ?? 0 }} P
                            </Badge>
                            <Badge v-if="(item.attendance_absent ?? 0) > 0" variant="destructive">
                                {{ item.attendance_absent }} A
                            </Badge>
                            <Badge v-if="(item.attendance_late ?? 0) > 0" variant="secondary" class="text-yellow-600">
                                {{ item.attendance_late }} L
                            </Badge>
                        </div>
                    </template>
                    <template #cell-status="{ item }">
                        <div class="flex items-center gap-2" @click.stop>
                            <Switch
                                :model-value="item.status"
                                @update:model-value="handleStatusToggle(item, $event)"
                            />
                            <span class="text-sm text-muted-foreground">
                                {{ item.status ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

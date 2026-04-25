<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard, ButtonGroup } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Plus, Tags, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Database } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { EmployeeTypeIndexProps, EmployeeTypeModel } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<EmployeeTypeIndexProps>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employee Types'), href: '/dashboard/employee-types' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

// Selection state
const selectedUuids = ref<(string | number)[]>([]);

// Bulk delete handler
const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/employee-types/bulk-delete?${params.toString()}`);
};

const columns: TableColumn<EmployeeTypeModel>[] = [
    {
        key: 'name',
        label: __('Name'),
        render: (type) => type.name,
    },
    {
        key: 'description',
        label: __('Description'),
        render: (type) => type.description || '-',
    },
    {
        key: 'employees_count',
        label: __('Employees'),
        render: (type) => type.employees_count?.toString() || '0',
    },
    {
        key: 'status',
        label: __('Status'),
        render: (type) => type.status ? __('Active') : __('Inactive'),
    },
];

const actions: TableAction<EmployeeTypeModel>[] = [
    {
        label: __('View'),
        icon: Eye,
        onClick: (type) => router.visit(`/dashboard/employee-types/${type.uuid}`),
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (type) => router.visit(`/dashboard/employee-types/${type.uuid}/edit`),
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (type) => router.visit(`/dashboard/employee-types/${type.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.employeeTypes.meta.current_page,
    last_page: props.employeeTypes.meta.last_page,
    per_page: props.employeeTypes.meta.per_page,
    total: props.employeeTypes.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/employee-types', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/employee-types', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/employee-types', getFilterParams(), { preserveState: true });
};

watch([statusFilter], () => {
    router.get('/dashboard/employee-types', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/employee-types/create');
};

const handleTrash = () => {
    router.visit('/dashboard/employee-types/trash');
};

const handleStatusToggle = (type: EmployeeTypeModel, newStatus: boolean) => {
    router.put(`/dashboard/employee-types/${type.uuid}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Employee Types')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    :title="__('Total Types')"
                    :value="props.stats.total"
                    :icon="Tags"
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

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Employee Types') }}</h2>
                        <p class="text-sm text-muted-foreground">{{ __('Manage employee type categories') }}</p>
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
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ __('Add Type') }}
                        </Button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search employee types...')"
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
                </div>

                <!-- Table -->
                <TableReusable
                    v-model:selected="selectedUuids"
                    :data="props.employeeTypes.data"
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
                    <template #cell-name="{ item }">
                        <div class="font-medium">{{ item.name }}</div>
                    </template>
                    <template #cell-employees_count="{ item }">
                        <Badge variant="secondary">
                            {{ item.employees_count || 0 }} {{ __('employees') }}
                        </Badge>
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

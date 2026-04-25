<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Plus, FileText, Clock, CheckCircle, XCircle, Search,
    Eye, Pencil, Trash2, ClipboardCheck,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { PermissionRequestIndexProps, PermissionRequest } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestIndexProps>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Permission Requests'), href: '/dashboard/permission-requests' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const typeFilter = ref(props.filters.type || 'all');

const columns: TableColumn<PermissionRequest>[] = [
    {
        key: 'employee',
        label: __('Employee'),
        render: (item) => item.employee?.full_name || '-',
    },
    {
        key: 'type',
        label: __('Type'),
        render: (item) => item.type_label,
    },
    {
        key: 'dates',
        label: __('Period'),
        render: (item) => `${item.from_date} - ${item.to_date}`,
    },
    {
        key: 'total_days',
        label: __('Days'),
        render: (item) => `${item.total_days} ${__('day(s)')}`,
    },
    {
        key: 'status',
        label: __('Status'),
        render: (item) => item.status_label,
    },
    {
        key: 'request_date',
        label: __('Requested'),
        render: (item) => item.request_date_formatted || '-',
    },
];

const actions: TableAction<PermissionRequest>[] = [
    {
        label: __('View'),
        icon: Eye,
        onClick: (item: PermissionRequest) => router.visit(`/dashboard/permission-requests/${item.uuid}`),
    },
    {
        label: __('Review'),
        icon: ClipboardCheck,
        onClick: (item: PermissionRequest) => router.visit(`/dashboard/permission-requests/${item.uuid}/review`),
        show: (item: PermissionRequest) => item.status === 'pending',
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (item: PermissionRequest) => router.visit(`/dashboard/permission-requests/${item.uuid}/edit`),
        show: (item: PermissionRequest) => item.status === 'pending',
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (item: PermissionRequest) => router.visit(`/dashboard/permission-requests/${item.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.permissionRequests.meta.current_page,
    last_page: props.permissionRequests.meta.last_page,
    per_page: props.permissionRequests.meta.per_page,
    total: props.permissionRequests.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/permission-requests', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/permission-requests', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/permission-requests', getFilterParams(), { preserveState: true });
};

watch([statusFilter, typeFilter], () => {
    router.get('/dashboard/permission-requests', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/permission-requests/create');
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'rejected':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'leave':
            return 'default';
        case 'overtime':
            return 'secondary';
        case 'remote':
            return 'outline';
        default:
            return 'secondary';
    }
};

const getInitials = (name: string | null | undefined) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Permission Requests" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard
                    :title="__('Total Requests')"
                    :value="props.stats.total"
                    :icon="FileText"
                />
                <StatsCard
                    :title="__('Pending')"
                    :value="props.stats.pending"
                    :icon="Clock"
                    variant="warning"
                />
                <StatsCard
                    :title="__('Approved')"
                    :value="props.stats.approved"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    :title="__('Rejected')"
                    :value="props.stats.rejected"
                    :icon="XCircle"
                    variant="destructive"
                />
            </div>

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Permission Requests') }}</h2>
                        <p class="text-sm text-muted-foreground">{{ __('Manage employee leave and permission requests') }}</p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('New Request') }}
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search requests...')"
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
                            <SelectItem v-for="(label, key) in props.statuses" :key="key" :value="key">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="typeFilter">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="__('Type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Types') }}</SelectItem>
                            <SelectItem v-for="(label, key) in props.types" :key="key" :value="key">
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table -->
                <TableReusable
                    :data="props.permissionRequests.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-employee="{ item }">
                        <div class="flex items-center gap-3">
                            <Avatar class="h-8 w-8">
                                <AvatarImage :src="item.employee?.avatar_url || ''" />
                                <AvatarFallback>{{ getInitials(item.employee?.full_name) }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-medium text-sm">{{ item.employee?.full_name || '-' }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.employee?.employee_code || '' }}</p>
                            </div>
                        </div>
                    </template>
                    <template #cell-type="{ item }">
                        <Badge :variant="getTypeVariant(item.type)">
                            {{ item.type_label }}
                        </Badge>
                    </template>
                    <template #cell-dates="{ item }">
                        <div class="text-sm">
                            <p>{{ item.from_date }}</p>
                            <p class="text-muted-foreground">to {{ item.to_date }}</p>
                        </div>
                    </template>
                    <template #cell-total_days="{ item }">
                        <Badge variant="outline">{{ item.total_days }} {{ __('day(s)') }}</Badge>
                    </template>
                    <template #cell-status="{ item }">
                        <Badge :variant="getStatusVariant(item.status)">
                            {{ item.status_label }}
                        </Badge>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

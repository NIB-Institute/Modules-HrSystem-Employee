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
import {
    Plus, Search, Pencil, Trash2,
    CalendarRange, CheckCircle2, CircleSlash, Users,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface AvailabilityRow {
    id: number;
    uuid: string;
    employee_id: number;
    day_of_week: string;
    day_label: string;
    start_time: string | null;
    end_time: string | null;
    is_active: boolean;
    notes: string | null;
    employee?: {
        id: number;
        full_name: string;
        employee_code: string;
    } | null;
}

interface Props {
    availabilities: {
        data: AvailabilityRow[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters: { search?: string; day_of_week?: string; is_active?: string };
    stats: {
        total: number;
        active: number;
        inactive: number;
        employees_covered: number;
    };
    days: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Availability'), href: '/dashboard/employee-availabilities' },
];

const search = ref(props.filters.search || '');
const dayFilter = ref(props.filters.day_of_week || 'all');
const activeFilter = ref(props.filters.is_active ?? 'all');

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);

const columns: TableColumn<AvailabilityRow>[] = [
    {
        key: 'employee',
        label: __('Employee'),
        render: (item) => item.employee?.full_name || '-',
    },
    { key: 'day', label: __('Day') },
    { key: 'time', label: __('Time') },
    { key: 'status', label: __('Status') },
    {
        key: 'notes',
        label: __('Notes'),
        render: (item) => item.notes || '-',
    },
];

const actions: TableAction<AvailabilityRow>[] = [
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (item) => router.visit(`/dashboard/employee-availabilities/${item.uuid}/edit`),
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (item) => router.visit(`/dashboard/employee-availabilities/${item.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.availabilities.meta.current_page,
    last_page: props.availabilities.meta.last_page,
    per_page: props.availabilities.meta.per_page,
    total: props.availabilities.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    day_of_week: dayFilter.value !== 'all' ? dayFilter.value : undefined,
    is_active: activeFilter.value !== 'all' ? activeFilter.value : undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/employee-availabilities', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/employee-availabilities', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/employee-availabilities', getFilterParams(), { preserveState: true });
};

watch([dayFilter, activeFilter], () => {
    router.get('/dashboard/employee-availabilities', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/employee-availabilities/create');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Employee Availability')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard
                    :title="__('Total Slots')"
                    :value="props.stats.total"
                    :icon="CalendarRange"
                />
                <StatsCard
                    :title="__('Active')"
                    :value="props.stats.active"
                    :icon="CheckCircle2"
                    variant="success"
                />
                <StatsCard
                    :title="__('Inactive')"
                    :value="props.stats.inactive"
                    :icon="CircleSlash"
                    variant="secondary"
                />
                <StatsCard
                    :title="__('Employees Covered')"
                    :value="props.stats.employees_covered"
                    :icon="Users"
                    variant="info"
                />
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Availability') }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ __('Recurring weekly working hours per employee') }}
                        </p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('New Slot') }}
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search by employee...')"
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <Select v-model="dayFilter">
                        <SelectTrigger class="w-[160px]">
                            <SelectValue :placeholder="__('Day')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Days') }}</SelectItem>
                            <SelectItem v-for="d in props.days" :key="d" :value="d">
                                {{ __(dayLabel(d)) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="activeFilter">
                        <SelectTrigger class="w-[160px]">
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
                    :data="props.availabilities.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-employee="{ item }">
                        <div v-if="item.employee">
                            <p class="text-sm font-medium">{{ item.employee.full_name }}</p>
                            <p class="text-xs text-muted-foreground">{{ item.employee.employee_code }}</p>
                        </div>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>
                    <template #cell-day="{ item }">
                        <Badge variant="outline">{{ item.day_label }}</Badge>
                    </template>
                    <template #cell-time="{ item }">
                        <span class="font-mono text-sm">
                            {{ item.start_time }} – {{ item.end_time }}
                        </span>
                    </template>
                    <template #cell-status="{ item }">
                        <Badge :variant="item.is_active ? 'default' : 'secondary'">
                            {{ item.is_active ? __('Active') : __('Inactive') }}
                        </Badge>
                    </template>
                    <template #cell-notes="{ item }">
                        <p v-if="item.notes" class="line-clamp-1 text-sm text-muted-foreground">
                            {{ item.notes }}
                        </p>
                        <span v-else class="text-muted-foreground">-</span>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

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
    Plus, Search, Pencil, Trash2, UserPlus, Eye,
    CalendarClock, CalendarDays, PlayCircle, CheckCircle,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface PlanRow {
    id: number;
    uuid: string;
    title: string;
    start_date: string | null;
    end_date: string | null;
    start_time: string | null;
    end_time: string | null;
    priority: string;
    status: string;
    location: string | null;
    valid_for_months: number | null;
    assignees_count: number;
}

interface Props {
    plans: {
        data: PlanRow[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters: { search?: string; status?: string; priority?: string };
    stats: {
        total: number;
        scheduled: number;
        in_progress: number;
        completed: number;
    };
    priorities: string[];
    statuses: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employee Plans'), href: '/dashboard/employee-plans' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const priorityFilter = ref(props.filters.priority || 'all');

const columns: TableColumn<PlanRow>[] = [
    { key: 'title', label: __('Title') },
    { key: 'period', label: __('Period') },
    { key: 'assignees', label: __('Employees') },
    { key: 'priority', label: __('Priority') },
    { key: 'status', label: __('Status') },
    { key: 'validity', label: __('Validity') },
];

const actions: TableAction<PlanRow>[] = [
    {
        label: __('View'),
        icon: Eye,
        onClick: (item) => router.visit(`/dashboard/employee-plans/${item.uuid}`),
    },
    {
        label: __('Assign Employees'),
        icon: UserPlus,
        onClick: (item) => router.visit(`/dashboard/employee-plan-assignments/create?employee_plan_id=${item.id}`),
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (item) => router.visit(`/dashboard/employee-plans/${item.uuid}/edit`),
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (item) => router.visit(`/dashboard/employee-plans/${item.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.plans.meta.current_page,
    last_page: props.plans.meta.last_page,
    per_page: props.plans.meta.per_page,
    total: props.plans.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    priority: priorityFilter.value !== 'all' ? priorityFilter.value : undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/employee-plans', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/employee-plans', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/employee-plans', getFilterParams(), { preserveState: true });
};

watch([statusFilter, priorityFilter], () => {
    router.get('/dashboard/employee-plans', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/employee-plans/create');
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed': return 'default';
        case 'cancelled': return 'destructive';
        case 'in_progress': return 'secondary';
        default: return 'outline';
    }
};

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'destructive';
        case 'high': return 'secondary';
        case 'low': return 'outline';
        default: return 'default';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Employee Plans')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard :title="__('Total Plans')" :value="props.stats.total" :icon="CalendarDays" />
                <StatsCard :title="__('Scheduled')" :value="props.stats.scheduled" :icon="CalendarClock" variant="info" />
                <StatsCard :title="__('In Progress')" :value="props.stats.in_progress" :icon="PlayCircle" variant="warning" />
                <StatsCard :title="__('Completed')" :value="props.stats.completed" :icon="CheckCircle" variant="success" />
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Employee Plans') }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ __('Plan templates — assign employees to a plan from the actions menu') }}
                        </p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('New Plan') }}
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search plans...')"
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[160px]">
                            <SelectValue :placeholder="__('Status')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Status') }}</SelectItem>
                            <SelectItem v-for="s in props.statuses" :key="s" :value="s">{{ __(s) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="priorityFilter">
                        <SelectTrigger class="w-[160px]">
                            <SelectValue :placeholder="__('Priority')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Priorities') }}</SelectItem>
                            <SelectItem v-for="p in props.priorities" :key="p" :value="p">{{ __(p) }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table -->
                <TableReusable
                    :data="props.plans.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-title="{ item }">
                        <div>
                            <p class="font-medium text-sm">{{ item.title }}</p>
                            <p v-if="item.location" class="text-xs text-muted-foreground">{{ item.location }}</p>
                        </div>
                    </template>
                    <template #cell-period="{ item }">
                        <div class="text-sm">
                            <p>{{ item.start_date }}</p>
                            <p class="text-muted-foreground">to {{ item.end_date }}</p>
                        </div>
                    </template>
                    <template #cell-assignees="{ item }">
                        <Badge variant="outline" class="font-mono">
                            {{ item.assignees_count }} {{ __('employees') }}
                        </Badge>
                    </template>
                    <template #cell-priority="{ item }">
                        <Badge :variant="getPriorityVariant(item.priority)">
                            {{ __(item.priority) }}
                        </Badge>
                    </template>
                    <template #cell-status="{ item }">
                        <Badge :variant="getStatusVariant(item.status)">
                            {{ __(item.status) }}
                        </Badge>
                    </template>
                    <template #cell-validity="{ item }">
                        <span v-if="item.valid_for_months" class="text-sm">
                            {{ item.valid_for_months }} {{ __('months') }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground">{{ __('No expiry') }}</span>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

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
    Plus, Search, Pencil, Trash2, Eye,
    UsersRound, ListChecks, PlayCircle, CheckCircle,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface AssignmentRow {
    id: number;
    uuid: string;
    employee_plan_id: number;
    employee_id: number;
    status: string;
    status_label: string;
    assigned_at: string | null;
    completed_at: string | null;
    expires_at: string | null;
    notes: string | null;
    plan?: { id: number; uuid: string; title: string; priority: string; status: string };
    employee?: { id: number; full_name: string; employee_code: string };
    availability?: { day_of_week: string; start_time: string | null; end_time: string | null } | null;
}

interface PlanOption {
    id: number;
    uuid: string;
    title: string;
}

interface Props {
    assignments: {
        data: AssignmentRow[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters: { search?: string; employee_plan_id?: string | number; status?: string };
    stats: { total: number; assigned: number; in_progress: number; completed: number };
    plans: PlanOption[];
    statuses: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Plan Assignments'), href: '/dashboard/employee-plan-assignments' },
];

const search = ref(props.filters.search || '');
const planFilter = ref(props.filters.employee_plan_id?.toString() || 'all');
const statusFilter = ref(props.filters.status || 'all');

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);

const columns: TableColumn<AssignmentRow>[] = [
    { key: 'employee', label: __('Employee') },
    { key: 'plan', label: __('Plan') },
    { key: 'slot', label: __('Slot') },
    { key: 'status', label: __('Status') },
    { key: 'expires', label: __('Expires') },
];

const actions: TableAction<AssignmentRow>[] = [
    {
        label: __('View'),
        icon: Eye,
        onClick: (item) => router.visit(`/dashboard/employee-plan-assignments/${item.uuid}`),
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (item) => router.visit(`/dashboard/employee-plan-assignments/${item.uuid}/edit`),
    },
    {
        label: __('Remove'),
        icon: Trash2,
        onClick: (item) => router.visit(`/dashboard/employee-plan-assignments/${item.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.assignments.meta.current_page,
    last_page: props.assignments.meta.last_page,
    per_page: props.assignments.meta.per_page,
    total: props.assignments.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
    employee_plan_id: planFilter.value !== 'all' ? planFilter.value : undefined,
    status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/employee-plan-assignments', {
        page,
        per_page: pagination.value.per_page,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/employee-plan-assignments', {
        per_page: perPage,
        ...getFilterParams(),
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/employee-plan-assignments', getFilterParams(), { preserveState: true });
};

watch([planFilter, statusFilter], () => {
    router.get('/dashboard/employee-plan-assignments', getFilterParams(), { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/employee-plan-assignments/create');
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed': return 'default';
        case 'expired':
        case 'dropped':
        case 'no_show': return 'destructive';
        case 'in_progress': return 'secondary';
        default: return 'outline';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Plan Assignments')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard :title="__('Total')" :value="props.stats.total" :icon="UsersRound" />
                <StatsCard :title="__('Assigned')" :value="props.stats.assigned" :icon="ListChecks" variant="info" />
                <StatsCard :title="__('In Progress')" :value="props.stats.in_progress" :icon="PlayCircle" variant="warning" />
                <StatsCard :title="__('Completed')" :value="props.stats.completed" :icon="CheckCircle" variant="success" />
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Plan Assignments') }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ __('Track which employees are on which plans, and their progress') }}
                        </p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('New Assignment') }}
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="__('Search employees or plans...')"
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <Select v-model="planFilter">
                        <SelectTrigger class="w-[220px]">
                            <SelectValue :placeholder="__('Plan')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Plans') }}</SelectItem>
                            <SelectItem
                                v-for="p in props.plans"
                                :key="p.id"
                                :value="p.id.toString()"
                            >
                                {{ p.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[160px]">
                            <SelectValue :placeholder="__('Status')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ __('All Status') }}</SelectItem>
                            <SelectItem v-for="s in props.statuses" :key="s" :value="s">
                                {{ __(s) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table -->
                <TableReusable
                    :data="props.assignments.data"
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
                    <template #cell-plan="{ item }">
                        <p class="text-sm font-medium">{{ item.plan?.title || '-' }}</p>
                    </template>
                    <template #cell-slot="{ item }">
                        <span v-if="item.availability" class="text-xs font-mono">
                            {{ __(dayLabel(item.availability.day_of_week)) }} ·
                            {{ item.availability.start_time }}–{{ item.availability.end_time }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground">{{ __('No slot') }}</span>
                    </template>
                    <template #cell-status="{ item }">
                        <Badge :variant="getStatusVariant(item.status)">
                            {{ item.status_label }}
                        </Badge>
                    </template>
                    <template #cell-expires="{ item }">
                        <span v-if="item.expires_at" class="text-sm">
                            {{ item.expires_at.slice(0, 10) }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

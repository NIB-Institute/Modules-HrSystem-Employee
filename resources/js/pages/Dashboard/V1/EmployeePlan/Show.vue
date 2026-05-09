<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft, Pencil, Trash2, UserPlus,
    Calendar, Clock, MapPin, Repeat, Hourglass, Flag,
    UsersRound, ListChecks, PlayCircle, CheckCircle, CircleSlash,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface PlanResource {
    id: number;
    uuid: string;
    title: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    start_time: string | null;
    end_time: string | null;
    priority: string;
    location: string | null;
    schedule_mode: string;
    is_recurring: boolean;
    recurrence_type: string | null;
    status: string;
    valid_for_months: number | null;
    assignees_count: number;
}

interface AssignmentRow {
    id: number;
    uuid: string;
    employee_id: number;
    status: string;
    status_label: string;
    assigned_at: string | null;
    completed_at: string | null;
    expires_at: string | null;
    notes: string | null;
    employee?: { id: number; full_name: string; employee_code: string } | null;
    availability?: { day_of_week: string; start_time: string | null; end_time: string | null } | null;
}

interface Props {
    plan: PlanResource;
    assignments: {
        data: AssignmentRow[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    stats: {
        total: number;
        assigned: number;
        in_progress: number;
        completed: number;
        expired: number;
    };
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employee Plans'), href: '/dashboard/employee-plans' },
    { title: props.plan.title, href: `/dashboard/employee-plans/${props.plan.uuid}` },
];

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);

const columns: TableColumn<AssignmentRow>[] = [
    { key: 'employee', label: __('Employee') },
    { key: 'slot', label: __('Slot') },
    { key: 'status', label: __('Status') },
    { key: 'assigned_at', label: __('Assigned') },
    { key: 'expires_at', label: __('Expires') },
];

const actions: TableAction<AssignmentRow>[] = [
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

const handlePageChange = (page: number) => {
    router.get(`/dashboard/employee-plans/${props.plan.uuid}`, {
        page,
        per_page: pagination.value.per_page,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get(`/dashboard/employee-plans/${props.plan.uuid}`, {
        per_page: perPage,
    }, { preserveState: true, preserveScroll: true });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'cancelled':
            return 'destructive';
        case 'in_progress':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgent':
            return 'destructive';
        case 'high':
            return 'secondary';
        case 'low':
            return 'outline';
        default:
            return 'default';
    }
};

const getAssignmentStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'expired':
        case 'dropped':
        case 'no_show':
            return 'destructive';
        case 'in_progress':
            return 'secondary';
        default:
            return 'outline';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.plan.title" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/dashboard/employee-plans">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">{{ props.plan.title }}</h1>
                            <Badge :variant="getStatusVariant(props.plan.status)">
                                {{ __(props.plan.status) }}
                            </Badge>
                            <Badge :variant="getPriorityVariant(props.plan.priority)">
                                {{ __(props.plan.priority) }}
                            </Badge>
                        </div>
                        <p v-if="props.plan.description" class="text-sm text-muted-foreground mt-1 max-w-2xl">
                            {{ props.plan.description }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="default" as-child>
                        <Link :href="`/dashboard/employee-plan-assignments/create?employee_plan_id=${props.plan.id}`">
                            <UserPlus class="mr-2 h-4 w-4" />
                            {{ __('Assign Employees') }}
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/employee-plans/${props.plan.uuid}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ __('Edit') }}
                        </Link>
                    </Button>
                    <Button variant="destructive" as-child>
                        <Link :href="`/dashboard/employee-plans/${props.plan.uuid}/delete`">
                            <Trash2 class="mr-2 h-4 w-4" />
                            {{ __('Delete') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Assignment stats -->
            <div class="grid gap-4 md:grid-cols-5">
                <StatsCard :title="__('Total')" :value="props.stats.total" :icon="UsersRound" />
                <StatsCard :title="__('Assigned')" :value="props.stats.assigned" :icon="ListChecks" variant="info" />
                <StatsCard :title="__('In Progress')" :value="props.stats.in_progress" :icon="PlayCircle" variant="warning" />
                <StatsCard :title="__('Completed')" :value="props.stats.completed" :icon="CheckCircle" variant="success" />
                <StatsCard :title="__('Expired')" :value="props.stats.expired" :icon="CircleSlash" variant="destructive" />
            </div>

            <!-- Plan details -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ __('Plan Details') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="flex items-start gap-2">
                            <Calendar class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Period') }}</dt>
                                <dd class="text-sm font-medium">
                                    {{ props.plan.start_date }} – {{ props.plan.end_date }}
                                </dd>
                            </div>
                        </div>
                        <div v-if="props.plan.start_time || props.plan.end_time" class="flex items-start gap-2">
                            <Clock class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Time') }}</dt>
                                <dd class="text-sm font-medium">
                                    {{ props.plan.start_time ?? '—' }} – {{ props.plan.end_time ?? '—' }}
                                </dd>
                            </div>
                        </div>
                        <div v-if="props.plan.location" class="flex items-start gap-2">
                            <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Location') }}</dt>
                                <dd class="text-sm font-medium">{{ props.plan.location }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <Flag class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Schedule Mode') }}</dt>
                                <dd class="text-sm font-medium">{{ __(props.plan.schedule_mode) }}</dd>
                            </div>
                        </div>
                        <div v-if="props.plan.is_recurring" class="flex items-start gap-2">
                            <Repeat class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Recurrence') }}</dt>
                                <dd class="text-sm font-medium">{{ __(props.plan.recurrence_type ?? '') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <Hourglass class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <dt class="text-xs text-muted-foreground">{{ __('Validity') }}</dt>
                                <dd class="text-sm font-medium">
                                    <span v-if="props.plan.valid_for_months">
                                        {{ props.plan.valid_for_months }} {{ __('months after completion') }}
                                    </span>
                                    <span v-else class="text-muted-foreground">{{ __('No expiry') }}</span>
                                </dd>
                            </div>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Assignments -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>{{ __('Assignments') }}</CardTitle>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ props.stats.total }}
                                {{ props.stats.total === 1 ? __('employee') : __('employees') }}
                                {{ __('on this plan') }}
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <TableReusable
                        :data="props.assignments.data"
                        :columns="columns"
                        :actions="actions"
                        :pagination="pagination"
                        :searchable="false"
                        empty-message="No employees assigned yet."
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
                        <template #cell-slot="{ item }">
                            <span v-if="item.availability" class="text-xs font-mono">
                                {{ __(dayLabel(item.availability.day_of_week)) }} ·
                                {{ item.availability.start_time }}–{{ item.availability.end_time }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground">{{ __('No slot') }}</span>
                        </template>
                        <template #cell-status="{ item }">
                            <Badge :variant="getAssignmentStatusVariant(item.status)">
                                {{ item.status_label }}
                            </Badge>
                        </template>
                        <template #cell-assigned_at="{ item }">
                            <span v-if="item.assigned_at" class="text-sm">
                                {{ item.assigned_at.slice(0, 10) }}
                            </span>
                            <span v-else class="text-muted-foreground">-</span>
                        </template>
                        <template #cell-expires_at="{ item }">
                            <span v-if="item.expires_at" class="text-sm">
                                {{ item.expires_at.slice(0, 10) }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground">-</span>
                        </template>
                    </TableReusable>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

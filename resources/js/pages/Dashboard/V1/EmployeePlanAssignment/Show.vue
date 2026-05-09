<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft, Pencil, Trash2,
    Calendar, CalendarRange, Clock, FileText, Hourglass,
    User, ClipboardList, ListChecks, ExternalLink,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface AssignmentResource {
    id: number;
    uuid: string;
    employee_plan_id: number;
    employee_id: number;
    employee_availability_id: number | null;
    status: string;
    status_label: string;
    assigned_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    expires_at: string | null;
    notes: string | null;
    created_at: string | null;
    updated_at: string | null;
    plan?: {
        id: number;
        uuid: string;
        title: string;
        priority: string;
        status: string;
        start_date: string | null;
        end_date: string | null;
        start_time: string | null;
        end_time: string | null;
    } | null;
    employee?: {
        id: number;
        uuid: string;
        full_name: string;
        first_name: string;
        last_name: string;
        employee_code: string;
        avatar_url: string | null;
    } | null;
    availability?: {
        id: number;
        uuid: string;
        day_of_week: string;
        start_time: string | null;
        end_time: string | null;
        is_active: boolean;
    } | null;
}

interface Props {
    assignment: AssignmentResource;
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Plan Assignments'), href: '/dashboard/employee-plan-assignments' },
    {
        title: props.assignment.employee?.full_name ?? __('Assignment'),
        href: `/dashboard/employee-plan-assignments/${props.assignment.uuid}`,
    },
]);

const dayLabel = (d: string) => d.charAt(0).toUpperCase() + d.slice(1);

const formatDate = (d?: string | null) => (d ? d.slice(0, 10) : null);
const formatDateTime = (d?: string | null) =>
    d ? new Date(d).toLocaleString() : null;

const isExpired = computed(() => props.assignment.status === 'expired');
const isCompleted = computed(() => props.assignment.status === 'completed');

const getStatusVariant = (status: string) => {
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

const initials = computed(() => {
    const first = props.assignment.employee?.first_name?.[0] ?? '';
    const last = props.assignment.employee?.last_name?.[0] ?? '';
    return `${first}${last}`.toUpperCase() || '?';
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${props.assignment.employee?.full_name ?? __('Assignment')} · ${props.assignment.plan?.title ?? ''}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/dashboard/employee-plan-assignments">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ props.assignment.employee?.full_name ?? __('Unknown employee') }}
                            </h1>
                            <Badge :variant="getStatusVariant(props.assignment.status)">
                                {{ props.assignment.status_label }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ __('Assigned to') }}
                            <Link
                                v-if="props.assignment.plan"
                                :href="`/dashboard/employee-plans/${props.assignment.plan.uuid}`"
                                class="font-medium text-foreground hover:underline"
                            >
                                {{ props.assignment.plan.title }}
                            </Link>
                            <span v-else class="text-muted-foreground">{{ __('a deleted plan') }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/employee-plan-assignments/${props.assignment.uuid}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ __('Edit') }}
                        </Link>
                    </Button>
                    <Button variant="destructive" as-child>
                        <Link :href="`/dashboard/employee-plan-assignments/${props.assignment.uuid}/delete`">
                            <Trash2 class="mr-2 h-4 w-4" />
                            {{ __('Remove') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left column: Employee + Availability -->
                <div class="space-y-6">
                    <!-- Employee card -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <User class="h-4 w-4" />
                                {{ __('Employee') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="props.assignment.employee" class="flex items-center gap-4">
                                <div v-if="props.assignment.employee.avatar_url" class="h-14 w-14 overflow-hidden rounded-full">
                                    <img
                                        :src="props.assignment.employee.avatar_url"
                                        :alt="props.assignment.employee.full_name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary"
                                >
                                    {{ initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium truncate">{{ props.assignment.employee.full_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ props.assignment.employee.employee_code }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                {{ __('No employee record.') }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Availability slot card -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <CalendarRange class="h-4 w-4" />
                                {{ __('Availability Slot') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="props.assignment.availability" class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <Badge variant="outline">
                                        {{ __(dayLabel(props.assignment.availability.day_of_week)) }}
                                    </Badge>
                                    <span class="font-mono text-sm">
                                        {{ props.assignment.availability.start_time }}
                                        – {{ props.assignment.availability.end_time }}
                                    </span>
                                </div>
                                <Badge
                                    v-if="!props.assignment.availability.is_active"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ __('Inactive') }}
                                </Badge>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                {{ __('No availability slot linked to this assignment.') }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right column: Plan + Timeline + Notes -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Plan info -->
                    <Card>
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2 text-base">
                                    <ClipboardList class="h-4 w-4" />
                                    {{ __('Plan') }}
                                </CardTitle>
                                <Button v-if="props.assignment.plan" variant="ghost" size="sm" as-child>
                                    <Link :href="`/dashboard/employee-plans/${props.assignment.plan.uuid}`">
                                        {{ __('Open plan') }}
                                        <ExternalLink class="ml-1 h-3 w-3" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="props.assignment.plan" class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-base font-semibold">{{ props.assignment.plan.title }}</p>
                                    <Badge :variant="getPriorityVariant(props.assignment.plan.priority)">
                                        {{ __(props.assignment.plan.priority) }}
                                    </Badge>
                                    <Badge variant="outline">
                                        {{ __(props.assignment.plan.status) }}
                                    </Badge>
                                </div>
                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div class="flex items-start gap-2">
                                        <Calendar class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-xs text-muted-foreground">{{ __('Period') }}</p>
                                            <p class="font-medium">
                                                {{ props.assignment.plan.start_date }}
                                                – {{ props.assignment.plan.end_date }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        v-if="props.assignment.plan.start_time || props.assignment.plan.end_time"
                                        class="flex items-start gap-2"
                                    >
                                        <Clock class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p class="text-xs text-muted-foreground">{{ __('Time') }}</p>
                                            <p class="font-medium">
                                                {{ props.assignment.plan.start_time ?? '—' }}
                                                – {{ props.assignment.plan.end_time ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                {{ __('Plan record no longer available.') }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Timeline -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <ListChecks class="h-4 w-4" />
                                {{ __('Timeline') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ol class="relative space-y-4 border-l border-muted pl-6">
                                <!-- Assigned -->
                                <li class="relative">
                                    <span class="absolute left-[-27px] flex h-4 w-4 items-center justify-center rounded-full bg-primary/15 ring-2 ring-background">
                                        <span class="h-1.5 w-1.5 rounded-full bg-primary" />
                                    </span>
                                    <p class="text-sm font-medium">{{ __('Assigned') }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDateTime(props.assignment.assigned_at) ?? '—' }}
                                    </p>
                                </li>

                                <!-- Started -->
                                <li v-if="props.assignment.started_at" class="relative">
                                    <span class="absolute left-[-27px] flex h-4 w-4 items-center justify-center rounded-full bg-amber-500/15 ring-2 ring-background">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                                    </span>
                                    <p class="text-sm font-medium">{{ __('Started') }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDateTime(props.assignment.started_at) }}
                                    </p>
                                </li>

                                <!-- Completed -->
                                <li v-if="props.assignment.completed_at" class="relative">
                                    <span class="absolute left-[-27px] flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500/15 ring-2 ring-background">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                    </span>
                                    <p class="text-sm font-medium">{{ __('Completed') }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDateTime(props.assignment.completed_at) }}
                                    </p>
                                </li>

                                <!-- Expires -->
                                <li v-if="props.assignment.expires_at" class="relative">
                                    <span
                                        class="absolute left-[-27px] flex h-4 w-4 items-center justify-center rounded-full ring-2 ring-background"
                                        :class="isExpired ? 'bg-destructive/15' : 'bg-muted'"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="isExpired ? 'bg-destructive' : 'bg-muted-foreground'"
                                        />
                                    </span>
                                    <p class="text-sm font-medium flex items-center gap-2">
                                        <Hourglass class="h-3.5 w-3.5" />
                                        {{ isExpired ? __('Expired') : __('Expires') }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(props.assignment.expires_at) }}
                                    </p>
                                </li>

                                <!-- No-progress fallback -->
                                <li
                                    v-if="!props.assignment.started_at && !props.assignment.completed_at && !isCompleted"
                                    class="relative pl-0 text-xs italic text-muted-foreground"
                                >
                                    {{ __('No progress recorded yet.') }}
                                </li>
                            </ol>
                        </CardContent>
                    </Card>

                    <!-- Notes -->
                    <Card v-if="props.assignment.notes">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <FileText class="h-4 w-4" />
                                {{ __('Notes') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="whitespace-pre-line text-sm text-muted-foreground">
                                {{ props.assignment.notes }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

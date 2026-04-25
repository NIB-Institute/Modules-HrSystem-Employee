<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import StatsCard from '@/components/shared/StatsCard/StatsCard.vue';
import {
    Users,
    Clock,
    TrendingUp,
    AlertTriangle,
    CheckCircle,
    Calendar,
    BarChart3,
    Filter,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface SelectOption {
    value: number;
    label: string;
}

interface Analytics {
    summary: {
        total_records: number;
        present: number;
        late: number;
        absent: number;
        on_leave: number;
        avg_work_hours: number;
        total_work_hours: number;
        unique_employees: number;
        total_employees: number;
        attendance_rate: number;
        working_days: number;
    };
    daily_attendance: Array<{
        date: string;
        day: string;
        total: number;
        present: number;
        late: number;
        absent: number;
        avg_hours: number;
    }>;
    status_distribution: Array<{
        status: string;
        label: string;
        count: number;
    }>;
    department_stats: Array<{
        department_id: number;
        department_name: string;
        department_code: string | null;
        total_attendance: number;
        present_count: number;
        avg_hours: number;
        total_hours: number;
    }>;
    top_employees: Array<{
        employee_id: number;
        employee_name: string;
        employee_code: string;
        avatar_url: string | null;
        department: string | null;
        days_worked: number;
        total_hours: number;
        avg_hours: number;
        late_count: number;
    }>;
    late_arrivals: Array<{
        employee_id: number;
        employee_name: string;
        employee_code: string;
        avatar_url: string | null;
        department: string | null;
        late_count: number;
    }>;
    filters: {
        start_date: string;
        end_date: string;
        department_id: number | null;
        employee_id: number | null;
    };
}

interface Props {
    analytics: Analytics;
    departmentOptions: SelectOption[];
    employeeOptions: SelectOption[];
}

import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<Props>();
const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Attendance'), href: '/dashboard/attendances' },
    { title: __('Analytics'), href: '/dashboard/attendances/analytics' },
];

// Filters
const startDate = ref(props.analytics.filters.start_date);
const endDate = ref(props.analytics.filters.end_date);
const departmentId = ref(props.analytics.filters.department_id ? String(props.analytics.filters.department_id) : 'all');
const employeeId = ref(props.analytics.filters.employee_id ? String(props.analytics.filters.employee_id) : 'all');

const applyFilters = () => {
    router.get('/dashboard/attendances/analytics', {
        start_date: startDate.value,
        end_date: endDate.value,
        department_id: departmentId.value !== 'all' ? departmentId.value : undefined,
        employee_id: employeeId.value !== 'all' ? employeeId.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Format hours to readable string
const formatHours = (hours: number): string => {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return `${h}h ${m}m`;
};

// Compute stats for status distribution chart
const statusColors: Record<string, string> = {
    present: 'bg-green-500',
    late: 'bg-yellow-500',
    absent: 'bg-red-500',
    early_leave: 'bg-orange-500',
    half_day: 'bg-blue-500',
    on_leave: 'bg-purple-500',
};

const totalStatusCount = computed(() => {
    return props.analytics.status_distribution.reduce((sum, s) => sum + s.count, 0);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Attendance Analytics')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ __('Attendance Analytics') }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Overview of attendance patterns and work hours') }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Filter class="h-4 w-4" />
                        {{ __('Filters') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <Label class="text-xs text-muted-foreground">{{ __('Start Date') }}</Label>
                            <Input type="date" v-model="startDate" />
                        </div>
                        <div class="flex-1">
                            <Label class="text-xs text-muted-foreground">{{ __('End Date') }}</Label>
                            <Input type="date" v-model="endDate" />
                        </div>
                        <div class="flex-1">
                            <Label class="text-xs text-muted-foreground">{{ __('Department') }}</Label>
                            <Select v-model="departmentId">
                                <SelectTrigger>
                                    <SelectValue :placeholder="__('All Departments')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ __('All Departments') }}</SelectItem>
                                    <SelectItem
                                        v-for="dept in departmentOptions"
                                        :key="dept.value"
                                        :value="String(dept.value)"
                                    >
                                        {{ dept.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex-1">
                            <Label class="text-xs text-muted-foreground">{{ __('Employee') }}</Label>
                            <Select v-model="employeeId">
                                <SelectTrigger>
                                    <SelectValue :placeholder="__('All Employees')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{{ __('All Employees') }}</SelectItem>
                                    <SelectItem
                                        v-for="emp in employeeOptions"
                                        :key="emp.value"
                                        :value="String(emp.value)"
                                    >
                                        {{ emp.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-end">
                            <Button @click="applyFilters">{{ __('Apply') }}</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <StatsCard
                    :title="__('Attendance Rate')"
                    :value="`${analytics.summary.attendance_rate}%`"
                    :icon="TrendingUp"
                    variant="info"
                />
                <StatsCard
                    :title="__('Present')"
                    :value="analytics.summary.present"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    :title="__('Late')"
                    :value="analytics.summary.late"
                    :icon="Clock"
                    variant="warning"
                />
                <StatsCard
                    :title="__('Absent')"
                    :value="analytics.summary.absent"
                    :icon="AlertTriangle"
                    variant="destructive"
                />
                <StatsCard
                    :title="__('Avg Work Hours')"
                    :value="formatHours(analytics.summary.avg_work_hours)"
                    :icon="BarChart3"
                />
            </div>

            <!-- Second Row Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ analytics.summary.unique_employees }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('Unique Employees') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                                <Clock class="h-5 w-5 text-blue-500" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ formatHours(analytics.summary.total_work_hours) }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('Total Work Hours') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10">
                                <Calendar class="h-5 w-5 text-green-500" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ analytics.summary.working_days }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('Working Days') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/10">
                                <Users class="h-5 w-5 text-purple-500" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ analytics.summary.on_leave }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('On Leave') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts and Tables -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Status Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('Status Distribution') }}</CardTitle>
                        <CardDescription>{{ __('Breakdown of attendance statuses') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="status in analytics.status_distribution"
                                :key="status.status"
                                class="flex items-center gap-3"
                            >
                                <div class="w-24 text-sm">{{ __(status.label) }}</div>
                                <div class="flex-1 bg-muted rounded-full h-4 overflow-hidden">
                                    <div
                                        :class="[statusColors[status.status] || 'bg-gray-400', 'h-full rounded-full transition-all']"
                                        :style="{ width: totalStatusCount > 0 ? `${(status.count / totalStatusCount) * 100}%` : '0%' }"
                                    />
                                </div>
                                <div class="w-12 text-sm text-right font-medium">{{ status.count }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Department Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('Department Performance') }}</CardTitle>
                        <CardDescription>{{ __('Attendance by department') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="dept in analytics.department_stats"
                                :key="dept.department_id"
                                class="flex items-center justify-between p-3 rounded-lg border"
                            >
                                <div>
                                    <p class="font-medium text-sm">{{ dept.department_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ dept.present_count }} {{ __('present') }} &middot; {{ __('Avg') }} {{ formatHours(dept.avg_hours) }}/{{ __('day') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-sm">{{ formatHours(dept.total_hours) }}</p>
                                    <p class="text-xs text-muted-foreground">{{ __('total hours') }}</p>
                                </div>
                            </div>
                            <div v-if="analytics.department_stats.length === 0" class="text-center py-8 text-muted-foreground">
                                {{ __('No department data available') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Employees -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('Top Performers') }}</CardTitle>
                        <CardDescription>{{ __('Employees with most work hours') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="(emp, index) in analytics.top_employees"
                                :key="emp.employee_id"
                                class="flex items-center gap-3 p-3 rounded-lg border"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm">{{ emp.employee_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ emp.department || 'N/A' }} &middot; {{ emp.days_worked }} {{ __('days') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-sm">{{ formatHours(emp.total_hours) }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatHours(emp.avg_hours) }}/{{ __('day') }}</p>
                                </div>
                            </div>
                            <div v-if="analytics.top_employees.length === 0" class="text-center py-8 text-muted-foreground">
                                {{ __('No employee data available') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Late Arrivals -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('Frequent Late Arrivals') }}</CardTitle>
                        <CardDescription>{{ __('Employees with most late check-ins') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="emp in analytics.late_arrivals"
                                :key="emp.employee_id"
                                class="flex items-center gap-3 p-3 rounded-lg border border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-950"
                            >
                                <div class="flex-1">
                                    <p class="font-medium text-sm">{{ emp.employee_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ emp.department || 'N/A' }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-yellow-600">
                                    <AlertTriangle class="h-4 w-4" />
                                    <span class="font-medium">{{ emp.late_count }}</span>
                                    <span class="text-xs">{{ __('times') }}</span>
                                </div>
                            </div>
                            <div v-if="analytics.late_arrivals.length === 0" class="text-center py-8 text-muted-foreground">
                                {{ __('No late arrivals in this period') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Daily Attendance Table -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ __('Daily Attendance') }}</CardTitle>
                    <CardDescription>{{ __('Day-by-day attendance records') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2 px-3 text-left font-medium">{{ __('Date') }}</th>
                                    <th class="py-2 px-3 text-left font-medium">{{ __('Day') }}</th>
                                    <th class="py-2 px-3 text-right font-medium">{{ __('Total') }}</th>
                                    <th class="py-2 px-3 text-right font-medium">{{ __('Present') }}</th>
                                    <th class="py-2 px-3 text-right font-medium">{{ __('Late') }}</th>
                                    <th class="py-2 px-3 text-right font-medium">{{ __('Absent') }}</th>
                                    <th class="py-2 px-3 text-right font-medium">{{ __('Avg Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="day in analytics.daily_attendance.slice(-14)"
                                    :key="day.date"
                                    class="border-b hover:bg-muted/50"
                                >
                                    <td class="py-2 px-3">{{ day.date }}</td>
                                    <td class="py-2 px-3">{{ __(day.day) }}</td>
                                    <td class="py-2 px-3 text-right">{{ day.total }}</td>
                                    <td class="py-2 px-3 text-right text-green-600">{{ day.present }}</td>
                                    <td class="py-2 px-3 text-right text-yellow-600">{{ day.late }}</td>
                                    <td class="py-2 px-3 text-right text-red-600">{{ day.absent }}</td>
                                    <td class="py-2 px-3 text-right">{{ formatHours(day.avg_hours) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

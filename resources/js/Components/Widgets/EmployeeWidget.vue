<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChartContainer, ChartTooltip, ChartCrosshair } from '@/components/ui/chart';
import {
    VisXYContainer,
    VisStackedBar,
    VisAxis,
    VisArea,
    VisLine,
} from '@unovis/vue';
import {
    Users,
    UserCheck,
    UserX,
    Calendar,
    RefreshCw,
    ArrowUpRight,
    Building2,
    Clock,
    Mail,
    Eye,
    TrendingUp,
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import type { ChartConfig } from '@/components/ui/chart';

// Types
export interface EmployeeMetrics {
    total: number;
    active: number;
    inactive: number;
    totalTypes: number;
    todayPresent: number;
    todayAbsent: number;
    attendanceRate: number;
    growthPercent: number;
}

export interface AttendanceTrendPoint {
    label: string;
    date: string;
    present: number;
    absent: number;
}

export interface GrowthTrendPoint {
    label: string;
    value: number;
}

export interface RecentEmployee {
    id: number;
    name: string;
    email: string;
    type: string;
    status: string;
    created_at: string;
}

export interface EmployeeWidgetProps {
    metrics: EmployeeMetrics;
    attendanceTrend: AttendanceTrendPoint[];
    growthTrend: GrowthTrendPoint[];
    recentEmployees?: RecentEmployee[];
    dateRange?: string;
    loading?: boolean;
    showStats?: boolean;
    showAttendance?: boolean;
    showGrowth?: boolean;
    showRecent?: boolean;
}

const props = withDefaults(defineProps<EmployeeWidgetProps>(), {
    dateRange: '30d',
    loading: false,
    showStats: true,
    showAttendance: true,
    showGrowth: true,
    showRecent: true,
});

const emit = defineEmits<{
    (e: 'dateRangeChange', value: string): void;
    (e: 'refresh'): void;
}>();

const selectedDateRange = ref(props.dateRange);

const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 Days' },
    { value: '30d', label: 'Last 30 Days' },
    { value: '90d', label: 'Last 90 Days' },
    { value: 'year', label: 'This Year' },
];

// Chart configs
const attendanceChartConfig: ChartConfig = {
    present: { label: 'Present', color: 'var(--chart-2)' },
    absent: { label: 'Absent', color: 'var(--chart-4)' },
};

const growthChartConfig: ChartConfig = {
    value: { label: 'New Employees', color: 'var(--chart-1)' },
};

// Computed
const growthIndicator = computed(() => ({
    isPositive: props.metrics.growthPercent >= 0,
    value: Math.abs(props.metrics.growthPercent),
}));

watch(selectedDateRange, (newValue) => {
    emit('dateRangeChange', newValue);
});

const handleRefresh = () => {
    emit('refresh');
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};

const formatPercent = (num: number) => {
    return `${num.toFixed(1)}%`;
};

const getStatusBadgeVariant = (status: string | boolean): 'default' | 'secondary' | 'destructive' | 'outline' => {
    // Handle boolean status (true = active, false = inactive)
    if (typeof status === 'boolean') {
        return status ? 'default' : 'secondary';
    }
    // Handle string status
    switch (status?.toLowerCase?.() ?? '') {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'suspended':
            return 'destructive';
        default:
            return 'outline';
    }
};

const formatStatus = (status: string | boolean): string => {
    if (typeof status === 'boolean') {
        return status ? 'Active' : 'Inactive';
    }
    return status || 'Unknown';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Employee Overview</h2>
                <p class="text-sm text-muted-foreground">Track employee attendance and growth</p>
            </div>
            <div class="flex items-center gap-2">
                <Select v-model="selectedDateRange">
                    <SelectTrigger class="w-[160px]">
                        <Calendar class="mr-2 h-4 w-4" />
                        <SelectValue placeholder="Select period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in dateRangeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="outline" size="icon" @click="handleRefresh" :disabled="loading">
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Employees</CardTitle>
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(metrics.total) }}</div>
                    <div class="flex items-center text-xs">
                        <ArrowUpRight
                            v-if="growthIndicator.isPositive"
                            class="mr-1 h-3 w-3 text-green-500"
                        />
                        <span :class="growthIndicator.isPositive ? 'text-green-500' : 'text-red-500'">
                            {{ growthIndicator.isPositive ? '+' : '-' }}{{ formatPercent(growthIndicator.value) }}
                        </span>
                        <span class="ml-1 text-muted-foreground">from last month</span>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Employees</CardTitle>
                    <UserCheck class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(metrics.active) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatNumber(metrics.inactive) }} inactive
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Today's Attendance</CardTitle>
                    <Calendar class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ formatNumber(metrics.todayPresent) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent(metrics.attendanceRate) }} attendance rate
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Employee Types</CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(metrics.totalTypes) }}</div>
                    <p class="text-xs text-muted-foreground">Different job roles</p>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Section -->
        <div v-if="showAttendance || showGrowth" class="grid gap-6 lg:grid-cols-2">
            <!-- Attendance Trend Chart -->
            <Card v-if="showAttendance">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="h-5 w-5" />
                        Attendance Trend (Last 7 Days)
                    </CardTitle>
                    <CardDescription>Daily attendance overview</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="attendanceChartConfig" class="h-[280px]">
                        <VisXYContainer :data="attendanceTrend" :margin="{ top: 10, bottom: 30, left: 40, right: 10 }">
                            <VisStackedBar
                                :x="(_: AttendanceTrendPoint, i: number) => i"
                                :y="[(d: AttendanceTrendPoint) => d.present, (d: AttendanceTrendPoint) => d.absent]"
                                :color="[attendanceChartConfig.present.color, attendanceChartConfig.absent.color]"
                                :bar-padding="0.2"
                                :rounded-corners="4"
                            />
                            <VisAxis
                                type="x"
                                :tick-format="(i: number) => attendanceTrend[i]?.label || ''"
                            />
                            <VisAxis type="y" :num-ticks="5" />
                        </VisXYContainer>
                    </ChartContainer>
                    <div class="flex justify-center gap-4 mt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded bg-chart-2"></div>
                            <span class="text-sm">Present</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded bg-chart-4"></div>
                            <span class="text-sm">Absent</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Growth Trend Chart -->
            <Card v-if="showGrowth">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5" />
                        Employee Growth (Last 6 Months)
                    </CardTitle>
                    <CardDescription>New employees over time</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="growthChartConfig" class="h-[280px]">
                        <VisXYContainer :data="growthTrend" :margin="{ top: 10, bottom: 30, left: 40, right: 10 }">
                            <VisArea
                                :x="(_: GrowthTrendPoint, i: number) => i"
                                :y="(d: GrowthTrendPoint) => d.value"
                                :color="growthChartConfig.value.color"
                                :opacity="0.3"
                            />
                            <VisLine
                                :x="(_: GrowthTrendPoint, i: number) => i"
                                :y="(d: GrowthTrendPoint) => d.value"
                                :color="growthChartConfig.value.color"
                                :line-width="2"
                            />
                            <VisAxis
                                type="x"
                                :tick-format="(i: number) => growthTrend[i]?.label || ''"
                            />
                            <VisAxis type="y" :num-ticks="5" />
                        </VisXYContainer>
                    </ChartContainer>
                </CardContent>
            </Card>
        </div>

        <!-- Recent Employees -->
        <Card v-if="showRecent && recentEmployees && recentEmployees.length > 0">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-primary" />
                            Recent Employees
                        </CardTitle>
                        <CardDescription>Latest employees added to the system</CardDescription>
                    </div>
                    <Link href="/dashboard/employees" class="text-sm text-primary hover:underline">
                        View All
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div
                        v-for="emp in recentEmployees"
                        :key="emp.id"
                        class="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-medium">
                                {{ emp.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ emp.name }}</p>
                                <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                    <Mail class="h-3 w-3 shrink-0" />
                                    <span class="truncate">{{ emp.email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="text-right">
                                <p class="text-sm text-muted-foreground">{{ emp.type }}</p>
                                <Badge :variant="getStatusBadgeVariant(emp.status)">
                                    {{ formatStatus(emp.status) }}
                                </Badge>
                            </div>
                            <Link
                                :href="`/dashboard/employees/${emp.id}`"
                                class="rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <Eye class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

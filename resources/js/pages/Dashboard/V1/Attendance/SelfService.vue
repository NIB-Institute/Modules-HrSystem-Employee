<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    ArrowLeft,
    Clock,
    LogIn,
    LogOut,
    MapPin,
    Briefcase,
    Building2,
    CheckCircle2,
    Timer,
    CalendarDays,
    AlertCircle,
    Loader2,
    Shield,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { SelfServiceProps } from '@employee/types';

interface ExtendedProps extends SelfServiceProps {
    isAdminMode?: boolean;
    selectedEmployeeId?: number;
}

const props = defineProps<ExtendedProps>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Self Service', href: '/dashboard/attendances/self-service' },
];

// Real-time clock
const currentTime = ref(new Date());
let clockInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    clockInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);

    // Try to get location on mount if geolocation is available
    if (navigator.geolocation && props.config.requireLocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                checkInForm.latitude = position.coords.latitude;
                checkInForm.longitude = position.coords.longitude;
                checkOutForm.latitude = position.coords.latitude;
                checkOutForm.longitude = position.coords.longitude;
                locationStatus.value = 'success';
            },
            () => {
                locationStatus.value = 'error';
            },
            { enableHighAccuracy: true }
        );
    }
});

onUnmounted(() => {
    if (clockInterval) {
        clearInterval(clockInterval);
    }
});

// Format time as HH:MM:SS
const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
});

// Format date as Day, Month Date, Year
const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

// Location status
const locationStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');

// Check-in form
const checkInForm = useForm({
    latitude: null as number | null,
    longitude: null as number | null,
    location: '',
    notes: '',
});

// Check-out form
const checkOutForm = useForm({
    latitude: null as number | null,
    longitude: null as number | null,
    location: '',
    notes: '',
});

// Get user initials
const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Calculate work duration in real-time
const workDuration = computed(() => {
    if (!props.todayAttendance?.check_in_time) return null;

    const [hours, minutes, seconds] = props.todayAttendance.check_in_time.split(':').map(Number);
    const checkIn = new Date();
    checkIn.setHours(hours, minutes, seconds || 0, 0);

    // If already checked out, use the stored work hours
    if (props.todayAttendance.check_out_time) {
        return props.todayAttendance.work_hours_formatted;
    }

    // Calculate live duration
    const diff = currentTime.value.getTime() - checkIn.getTime();
    const totalMinutes = Math.floor(diff / 60000);
    const h = Math.floor(totalMinutes / 60);
    const m = totalMinutes % 60;

    return `${h}h ${m}m`;
});

// Get status badge variant
const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'present':
            return 'default';
        case 'late':
        case 'early_leave':
            return 'secondary';
        case 'absent':
            return 'destructive';
        default:
            return 'outline';
    }
};

// Build URL with employee_id for admin mode
const buildUrl = (base: string) => {
    if (props.isAdminMode && props.selectedEmployeeId) {
        return `${base}?employee_id=${props.selectedEmployeeId}`;
    }
    return base;
};

// Handle check-in
const handleCheckIn = () => {
    checkInForm.post(buildUrl('/dashboard/attendances/self-service/check-in'), {
        preserveScroll: true,
    });
};

// Handle check-out
const handleCheckOut = () => {
    checkOutForm.post(buildUrl('/dashboard/attendances/self-service/check-out'), {
        preserveScroll: true,
    });
};

// Request location
const requestLocation = () => {
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        return;
    }

    locationStatus.value = 'loading';
    navigator.geolocation.getCurrentPosition(
        (position) => {
            checkInForm.latitude = position.coords.latitude;
            checkInForm.longitude = position.coords.longitude;
            checkOutForm.latitude = position.coords.latitude;
            checkOutForm.longitude = position.coords.longitude;
            locationStatus.value = 'success';
        },
        () => {
            locationStatus.value = 'error';
        },
        { enableHighAccuracy: true }
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Self Service Attendance" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Flash Messages -->
            <Alert v-if="flash.success" variant="default" class="border-green-500 bg-green-50 dark:bg-green-950">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <AlertDescription class="text-green-700 dark:text-green-300">
                    {{ flash.success }}
                </AlertDescription>
            </Alert>

            <Alert v-if="flash.error" variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <!-- Admin Mode Banner -->
            <div
                v-if="isAdminMode"
                class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950"
            >
                <div class="flex items-center gap-3">
                    <Shield class="h-5 w-5 text-amber-600" />
                    <div>
                        <p class="font-medium text-amber-800 dark:text-amber-200">Admin Mode</p>
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Viewing as: {{ employee.full_name }} ({{ employee.employee_code }})
                        </p>
                    </div>
                </div>
                <a
                    href="/dashboard/attendances/self-service"
                    class="flex items-center gap-1 text-sm text-amber-700 hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Change Employee
                </a>
            </div>

            <!-- Header with Real-time Clock -->
            <div class="text-center">
                <div class="mb-2 text-6xl font-bold tracking-tight md:text-8xl">
                    {{ formattedTime }}
                </div>
                <div class="flex items-center justify-center gap-2 text-muted-foreground">
                    <CalendarDays class="h-4 w-4" />
                    <span>{{ formattedDate }}</span>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Employee Info Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Briefcase class="h-5 w-5" />
                            Employee Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-start gap-4">
                            <Avatar class="h-16 w-16">
                                <AvatarImage :src="employee.avatar_url ?? ''" :alt="employee.full_name" />
                                <AvatarFallback class="text-lg">
                                    {{ getInitials(employee.full_name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="flex-1 space-y-1">
                                <h3 class="text-xl font-semibold">{{ employee.full_name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ employee.employee_code }}</p>
                                <div v-if="employee.job_title" class="flex items-center gap-1 text-sm text-muted-foreground">
                                    <Briefcase class="h-3 w-3" />
                                    {{ employee.job_title }}
                                </div>
                                <div v-if="employee.department_name" class="flex items-center gap-1 text-sm text-muted-foreground">
                                    <Building2 class="h-3 w-3" />
                                    {{ employee.department_name }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Today's Attendance Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5" />
                            Today's Attendance
                        </CardTitle>
                        <CardDescription>
                            {{ formattedDate }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="todayAttendance" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <Badge :variant="getStatusVariant(todayAttendance.status)">
                                    {{ todayAttendance.status_label }}
                                </Badge>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Check In</p>
                                    <p class="text-lg font-semibold">
                                        {{ todayAttendance.check_in_time || '--:--' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Check Out</p>
                                    <p class="text-lg font-semibold">
                                        {{ todayAttendance.check_out_time || '--:--' }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="workDuration" class="flex items-center gap-2 rounded-lg bg-muted p-3">
                                <Timer class="h-5 w-5 text-primary" />
                                <div>
                                    <p class="text-sm text-muted-foreground">Work Duration</p>
                                    <p class="text-xl font-bold">{{ workDuration }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center text-muted-foreground">
                            <Clock class="mx-auto mb-2 h-12 w-12 opacity-50" />
                            <p>No attendance record for today</p>
                            <p class="text-sm">Check in to start your work day</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Action Card -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>
                        <span v-if="state.canCheckIn">Ready to start your work day?</span>
                        <span v-else-if="state.canCheckOut">Ready to end your work day?</span>
                        <span v-else-if="state.isCompleted">You've completed your attendance for today.</span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Location Status -->
                    <div v-if="config.requireLocation" class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="requestLocation"
                            :disabled="locationStatus === 'loading'"
                        >
                            <Loader2 v-if="locationStatus === 'loading'" class="mr-2 h-4 w-4 animate-spin" />
                            <MapPin v-else class="mr-2 h-4 w-4" />
                            {{ locationStatus === 'success' ? 'Location Captured' : 'Get Location' }}
                        </Button>
                        <Badge v-if="locationStatus === 'success'" variant="default" class="bg-green-500">
                            <CheckCircle2 class="mr-1 h-3 w-3" />
                            Ready
                        </Badge>
                        <Badge v-else-if="locationStatus === 'error'" variant="destructive">
                            <AlertCircle class="mr-1 h-3 w-3" />
                            Failed
                        </Badge>
                    </div>

                    <!-- Notes Input -->
                    <div v-if="state.canCheckIn || state.canCheckOut" class="space-y-2">
                        <Label>Notes (Optional)</Label>
                        <Textarea
                            v-if="state.canCheckIn"
                            v-model="checkInForm.notes"
                            placeholder="Add any notes for check-in..."
                            rows="2"
                        />
                        <Textarea
                            v-else-if="state.canCheckOut"
                            v-model="checkOutForm.notes"
                            placeholder="Add any notes for check-out..."
                            rows="2"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button
                            v-if="state.canCheckIn"
                            size="lg"
                            class="flex-1 bg-green-600 hover:bg-green-700"
                            @click="handleCheckIn"
                            :disabled="checkInForm.processing || (config.requireLocation && locationStatus !== 'success')"
                        >
                            <Loader2 v-if="checkInForm.processing" class="mr-2 h-5 w-5 animate-spin" />
                            <LogIn v-else class="mr-2 h-5 w-5" />
                            Check In
                        </Button>

                        <Button
                            v-if="state.canCheckOut"
                            size="lg"
                            class="flex-1 bg-red-600 hover:bg-red-700"
                            @click="handleCheckOut"
                            :disabled="checkOutForm.processing || (config.requireLocation && locationStatus !== 'success')"
                        >
                            <Loader2 v-if="checkOutForm.processing" class="mr-2 h-5 w-5 animate-spin" />
                            <LogOut v-else class="mr-2 h-5 w-5" />
                            Check Out
                        </Button>

                        <div
                            v-if="state.isCompleted"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-green-50 p-4 text-green-700 dark:bg-green-950 dark:text-green-300"
                        >
                            <CheckCircle2 class="h-6 w-6" />
                            <span class="font-medium">Attendance completed for today</span>
                        </div>
                    </div>

                    <!-- Work Schedule Info -->
                    <div class="mt-4 rounded-lg border bg-muted/50 p-3">
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Clock class="h-4 w-4" />
                            <span>Work Hours: {{ config.workStartTime }} - {{ config.workEndTime }}</span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Late threshold: {{ config.lateThresholdMinutes }} minutes after {{ config.workStartTime }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Attendance History Preview -->
            <Card v-if="todayAttendance">
                <CardHeader>
                    <CardTitle>Attendance Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-muted-foreground">Check-in Location</dt>
                            <dd class="flex items-center gap-1">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                {{ todayAttendance.check_in_location || 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-muted-foreground">Check-out Location</dt>
                            <dd class="flex items-center gap-1">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                {{ todayAttendance.check_out_location || 'Not recorded' }}
                            </dd>
                        </div>
                        <div v-if="todayAttendance.notes" class="sm:col-span-2">
                            <dt class="text-sm text-muted-foreground">Notes</dt>
                            <dd class="whitespace-pre-wrap">{{ todayAttendance.notes }}</dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

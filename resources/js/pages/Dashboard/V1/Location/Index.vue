<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import StatsCard from '@/components/shared/StatsCard/StatsCard.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    MapPin,
    Plus,
    Search,
    Pencil,
    Trash2,
    ExternalLink,
    Shield,
    MoreVertical,
    CalendarClock,
    Clock,
    MapPinned,
    CheckCircle,
    XCircle,
    Building2,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface School {
    id: number;
    uuid: string;
    name: string;
}

interface Location {
    id: number;
    uuid: string;
    name: string;
    code: string | null;
    type: string;
    city: string | null;
    country: string | null;
    latitude: number;
    longitude: number;
    geofence_radius: number;
    geofence_type: 'circle' | 'polygon' | 'dynamic';
    polygon_coordinates: [number, number][] | null;
    enforce_geofence: boolean;
    timezone: string;
    is_active: boolean;
    operating_hours: Record<string, { start: string; end: string }> | null;
    school: School | null;
}

interface Props {
    locationsList: {
        data: Location[];
        meta?: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters: {
        search: string | null;
        type: string | null;
        status: string | null;
    };
    types: Record<string, string>;
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Attendance'), href: '/dashboard/attendances' },
    { title: __('Locations'), href: '/dashboard/locations' },
];

const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'all');
const statusFilter = ref(props.filters.status || 'all');
const showDeleteDialog = ref(false);
const showScheduleDialog = ref(false);
const selectedLocation = ref<Location | null>(null);
const isSavingSchedule = ref(false);

// Days of the week
const daysOfWeek = [
    { key: 'monday', label: __('Monday'), short: __('Mon') },
    { key: 'tuesday', label: __('Tuesday'), short: __('Tue') },
    { key: 'wednesday', label: __('Wednesday'), short: __('Wed') },
    { key: 'thursday', label: __('Thursday'), short: __('Thu') },
    { key: 'friday', label: __('Friday'), short: __('Fri') },
    { key: 'saturday', label: __('Saturday'), short: __('Sat') },
    { key: 'sunday', label: __('Sunday'), short: __('Sun') },
];

// Default operating hours
const defaultOperatingHours: Record<string, { enabled: boolean; start: string; end: string }> = {
    monday: { enabled: true, start: '08:00', end: '17:00' },
    tuesday: { enabled: true, start: '08:00', end: '17:00' },
    wednesday: { enabled: true, start: '08:00', end: '17:00' },
    thursday: { enabled: true, start: '08:00', end: '17:00' },
    friday: { enabled: true, start: '08:00', end: '17:00' },
    saturday: { enabled: true, start: '08:00', end: '12:00' },
    sunday: { enabled: true, start: '08:00', end: '12:00' },
};

// Schedule state
const scheduleEnabled = ref(false);
const operatingHours = ref<Record<string, { enabled: boolean; start: string; end: string }>>({ ...defaultOperatingHours });

const locations = computed(() => props.locationsList.data || []);
const pagination = computed(() => props.locationsList.meta);

// Pagination handlers
const goToPage = (page: number) => {
    router.get('/dashboard/locations', {
        page,
        search: searchQuery.value || undefined,
        type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Stats computed properties
const stats = computed(() => {
    const all = locations.value;
    return {
        total: all.length,
        active: all.filter(l => l.is_active).length,
        inactive: all.filter(l => !l.is_active).length,
        enforced: all.filter(l => l.enforce_geofence).length,
    };
});

// Format distance in meters or kilometers
const formatDistance = (meters: number): string => {
    if (meters >= 1000) {
        const km = meters / 1000;
        return km % 1 === 0 ? `${km}km` : `${km.toFixed(1)}km`;
    }
    return `${meters}m`;
};

// Calculate polygon area using Shoelace formula
const calculatePolygonArea = (coords: [number, number][]): number => {
    if (!coords || coords.length < 3) return 0;

    const n = coords.length;
    const avgLat = coords.reduce((sum, c) => sum + c[0], 0) / n;
    const latScale = 111000; // meters per degree latitude
    const lngScale = 111000 * Math.cos((avgLat * Math.PI) / 180);

    let area = 0;
    for (let i = 0; i < n; i++) {
        const j = (i + 1) % n;
        const x1 = coords[i][1] * lngScale;
        const y1 = coords[i][0] * latScale;
        const x2 = coords[j][1] * lngScale;
        const y2 = coords[j][0] * latScale;
        area += (x1 * y2) - (x2 * y1);
    }

    return Math.abs(area / 2);
};

// Format area to display
const formatArea = (area: number): string => {
    if (area >= 1000000) {
        return `${(area / 1000000).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} km²`;
    }
    return `${area.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} m²`;
};

// Haversine distance calculation
const haversineDistance = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// Calculate polygon perimeter
const calculatePolygonPerimeter = (coords: [number, number][]): number => {
    if (!coords || coords.length < 2) return 0;

    let perimeter = 0;
    for (let i = 0; i < coords.length; i++) {
        const j = (i + 1) % coords.length;
        perimeter += haversineDistance(coords[i][0], coords[i][1], coords[j][0], coords[j][1]);
    }
    return perimeter;
};

// Format perimeter to display
const formatPerimeter = (perimeter: number): string => {
    if (perimeter >= 1000) {
        return `${(perimeter / 1000).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} km`;
    }
    return `${perimeter.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} m`;
};

// Get geofence display info for a location
const getGeofenceInfo = (location: Location): string => {
    if (location.geofence_type === 'polygon' && location.polygon_coordinates) {
        const area = calculatePolygonArea(location.polygon_coordinates);
        return formatArea(area);
    }
    return formatDistance(location.geofence_radius);
};

const handleSearch = () => {
    router.get('/dashboard/locations', {
        search: searchQuery.value || undefined,
        type: typeFilter.value !== 'all' ? typeFilter.value : undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleFilter = () => {
    handleSearch();
};

const openDeleteDialog = (location: Location) => {
    selectedLocation.value = location;
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    if (!selectedLocation.value) return;
    router.delete(`/dashboard/locations/${selectedLocation.value.uuid}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedLocation.value = null;
        },
    });
};

const openInMaps = (lat: number, lng: number) => {
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
};

// Schedule dialog functions
const openScheduleDialog = (location: Location) => {
    selectedLocation.value = location;

    // Initialize from existing data
    if (location.operating_hours) {
        scheduleEnabled.value = true;
        const hours = { ...defaultOperatingHours };
        for (const day of daysOfWeek) {
            const existingHours = location.operating_hours[day.key];
            if (existingHours && typeof existingHours === 'object' && 'start' in existingHours) {
                hours[day.key] = {
                    enabled: true,
                    start: existingHours.start,
                    end: existingHours.end,
                };
            } else {
                hours[day.key].enabled = false;
            }
        }
        operatingHours.value = hours;
    } else {
        scheduleEnabled.value = false;
        operatingHours.value = { ...defaultOperatingHours };
    }

    showScheduleDialog.value = true;
};

const closeScheduleDialog = () => {
    showScheduleDialog.value = false;
    selectedLocation.value = null;
};

const saveSchedule = () => {
    if (!selectedLocation.value) return;

    isSavingSchedule.value = true;

    // Build operating hours data
    let operatingHoursData: Record<string, { start: string; end: string }> | null = null;

    if (scheduleEnabled.value) {
        const data: Record<string, { start: string; end: string }> = {};

        // Add daily hours
        for (const day of daysOfWeek) {
            if (operatingHours.value[day.key].enabled) {
                data[day.key] = {
                    start: operatingHours.value[day.key].start,
                    end: operatingHours.value[day.key].end,
                };
            }
        }

        operatingHoursData = Object.keys(data).length > 0 ? data : null;
    }

    router.put(`/dashboard/locations/${selectedLocation.value.uuid}/schedule`, {
        operating_hours: operatingHoursData,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeScheduleDialog();
        },
        onFinish: () => {
            isSavingSchedule.value = false;
        },
    });
};

// Check if location has schedule
const hasSchedule = (location: Location) => {
    return location.operating_hours && Object.keys(location.operating_hours).length > 0;
};

// Toggle location active status
const handleStatusToggle = (location: Location, newStatus: boolean) => {
    router.put(`/dashboard/locations/${location.uuid}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Locations - Geofence Management')" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">{{ __('Scan Locations') }}</h1>
                    <p class="text-sm text-muted-foreground">{{ __('Manage geofence locations for attendance') }}</p>
                </div>
                <Button as-child>
                    <Link href="/dashboard/locations/create">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('Add Location') }}
                    </Link>
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatsCard
                    :title="__('Total Locations')"
                    :value="stats.total"
                    :icon="MapPin"
                />
                <StatsCard
                    :title="__('Active')"
                    :value="stats.active"
                    :icon="CheckCircle"
                    variant="success"
                    value-color="text-green-600"
                />
                <StatsCard
                    :title="__('Inactive')"
                    :value="stats.inactive"
                    :icon="XCircle"
                    variant="secondary"
                    value-color="text-gray-500"
                />
                <StatsCard
                    :title="__('Geofence Enforced')"
                    :value="stats.enforced"
                    :icon="Shield"
                    variant="info"
                    value-color="text-blue-600"
                />
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        :placeholder="__('Search locations...')"
                        class="pl-9"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Select v-model="typeFilter" @update:modelValue="handleFilter">
                    <SelectTrigger class="w-[140px]">
                        <SelectValue :placeholder="__('All Types')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ __('All Types') }}</SelectItem>
                        <SelectItem v-for="(label, key) in types" :key="key" :value="key">
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="statusFilter" @update:modelValue="handleFilter">
                    <SelectTrigger class="w-[140px]">
                        <SelectValue :placeholder="__('All Status')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ __('All Status') }}</SelectItem>
                        <SelectItem value="active">{{ __('Active') }}</SelectItem>
                        <SelectItem value="inactive">{{ __('Inactive') }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Locations Grid -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="location in locations"
                    :key="location.id"
                    :class="[
                        'group relative rounded-lg border bg-card p-4 transition-all hover:border-primary/50 hover:shadow-sm',
                        !location.is_active && 'bg-muted/30'
                    ]"
                >
                    <!-- Header Row -->
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium truncate">{{ location.name }}</h3>
                                <span
                                    :class="[
                                        'inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium',
                                        location.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                    ]"
                                >
                                    {{ location.is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-muted-foreground">{{ types[location.type] || location.type }}</span>
                                <span v-if="location.code" class="text-xs text-muted-foreground">·</span>
                                <span v-if="location.code" class="text-xs font-mono text-muted-foreground">{{ location.code }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Switch
                                :model-value="location.is_active"
                                @update:model-value="handleStatusToggle(location, $event)"
                            />
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/dashboard/locations/${location.uuid}/edit`" class="flex items-center gap-2">
                                            <Pencil class="h-4 w-4" />
                                            {{ __('Edit Location') }}
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="openScheduleDialog(location)" class="flex items-center gap-2">
                                        <CalendarClock class="h-4 w-4" />
                                        {{ __('Manage Schedule') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="openDeleteDialog(location)"
                                        class="flex items-center gap-2 text-destructive"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        {{ __('Delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-muted-foreground mb-0.5">{{ __('School') }}</p>
                            <div class="flex items-center gap-1.5">
                                <Building2 class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                <span class="truncate">{{ location.school?.name || __('No School') }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground mb-0.5">{{ __('Location') }}</p>
                            <div class="flex items-center gap-1.5">
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                <span class="truncate">{{ location.city || __('Unknown') }}</span>
                            </div>
                        </div>
                        <!-- Circle/Dynamic Geofence -->
                        <div v-if="location.geofence_type !== 'polygon'" class="col-span-2">
                            <p class="text-xs text-muted-foreground mb-0.5">{{ __('Geofence Radius') }}</p>
                            <div class="flex items-center gap-1.5">
                                <Shield :class="['h-3.5 w-3.5 shrink-0', location.enforce_geofence ? 'text-green-500' : 'text-muted-foreground']" />
                                <span>{{ formatDistance(location.geofence_radius) }}</span>
                                <span class="text-muted-foreground text-xs">({{ location.enforce_geofence ? __('Required') : __('Optional') }})</span>
                            </div>
                        </div>
                        <!-- Polygon Geofence -->
                        <template v-else-if="location.polygon_coordinates">
                            <div>
                                <p class="text-xs text-muted-foreground mb-0.5">{{ __('Total Area') }}</p>
                                <div class="flex items-center gap-1.5">
                                    <Shield :class="['h-3.5 w-3.5 shrink-0', location.enforce_geofence ? 'text-green-500' : 'text-muted-foreground']" />
                                    <span class="font-medium">{{ formatArea(calculatePolygonArea(location.polygon_coordinates)) }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground mb-0.5">{{ __('Total Distance') }}</p>
                                <div class="flex items-center gap-1.5">
                                    <MapPin class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                                    <span class="font-medium">{{ formatPerimeter(calculatePolygonPerimeter(location.polygon_coordinates)) }}</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between mt-3 pt-3 border-t">
                        <div class="flex items-center gap-3 text-xs text-muted-foreground">
                            <span v-if="hasSchedule(location)" class="flex items-center gap-1">
                                <Clock class="h-3 w-3 text-blue-500" />
                                {{ __('Schedule set') }}
                            </span>
                            <span v-else>{{ __('No schedule') }}</span>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-7 text-xs"
                            @click="openInMaps(Number(location.latitude), Number(location.longitude))"
                        >
                            <ExternalLink class="h-3 w-3 mr-1" />
                            {{ __('View Map') }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between border-t pt-4">
                <p class="text-sm text-muted-foreground">
                    {{ __('Showing') }} {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}
                    {{ __('to') }} {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                    {{ __('of') }} {{ pagination.total }} {{ __('locations') }}
                </p>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="pagination.current_page === 1"
                        @click="goToPage(pagination.current_page - 1)"
                    >
                        <ChevronLeft class="h-4 w-4 mr-1" />
                        {{ __('Previous') }}
                    </Button>
                    <div class="flex items-center gap-1">
                        <template v-for="page in pagination.last_page" :key="page">
                            <Button
                                v-if="page === 1 || page === pagination.last_page || (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)"
                                :variant="page === pagination.current_page ? 'default' : 'outline'"
                                size="sm"
                                class="w-9"
                                @click="goToPage(page)"
                            >
                                {{ page }}
                            </Button>
                            <span
                                v-else-if="page === pagination.current_page - 2 || page === pagination.current_page + 2"
                                class="px-2 text-muted-foreground"
                            >
                                ...
                            </span>
                        </template>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="pagination.current_page === pagination.last_page"
                        @click="goToPage(pagination.current_page + 1)"
                    >
                        {{ __('Next') }}
                        <ChevronRight class="h-4 w-4 ml-1" />
                    </Button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="locations.length === 0" class="flex flex-col items-center justify-center py-12">
                <MapPinned class="h-12 w-12 text-muted-foreground mb-4" />
                <h3 class="font-medium mb-1">{{ __('No locations found') }}</h3>
                <p class="text-sm text-muted-foreground mb-4">{{ __('Create your first scan location') }}</p>
                <Button as-child size="sm">
                    <Link href="/dashboard/locations/create">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('Add Location') }}
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ __('Delete Location') }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ __('Are you sure you want to delete') }} "{{ selectedLocation?.name }}"?
                        {{ __('This action cannot be undone.') }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>{{ __('Cancel') }}</AlertDialogCancel>
                    <AlertDialogAction
                        @click="handleDelete"
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    >
                        {{ __('Delete') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Schedule Management Dialog -->
        <Dialog v-model:open="showScheduleDialog">
            <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
                <DialogHeader class="border-b pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                            <CalendarClock class="h-5 w-5 text-blue-500" />
                        </div>
                        <div>
                            <DialogTitle>{{ __('Manage Schedule') }}</DialogTitle>
                            <DialogDescription>
                                {{ selectedLocation?.name }}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <!-- Enable Schedule Toggle -->
                    <div :class="[
                        'flex items-center justify-between rounded-lg border p-4 transition-colors',
                        scheduleEnabled
                            ? 'bg-blue-500/5 border-blue-500/30'
                            : 'bg-muted/30'
                    ]">
                        <div>
                            <Label class="text-base font-medium">{{ __('Enable Operating Hours') }}</Label>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ scheduleEnabled
                                    ? __('Scanning allowed only during set hours')
                                    : __('No time restrictions for attendance')
                                }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            :variant="scheduleEnabled ? 'default' : 'outline'"
                            size="sm"
                            :class="scheduleEnabled ? 'bg-blue-500 hover:bg-blue-600' : ''"
                            @click="scheduleEnabled = !scheduleEnabled"
                        >
                            <Clock class="h-4 w-4 mr-1.5" />
                            {{ scheduleEnabled ? __('Enabled') : __('Disabled') }}
                        </Button>
                    </div>

                    <!-- Daily Hours -->
                    <div v-if="scheduleEnabled" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label class="text-sm font-medium">{{ __('Daily Operating Hours') }}</Label>
                            <span class="text-xs text-muted-foreground">{{ __('Check days to enable') }}</span>
                        </div>
                        <div class="rounded-lg border divide-y">
                            <div
                                v-for="day in daysOfWeek"
                                :key="day.key"
                                :class="[
                                    'flex items-center gap-3 p-3 transition-colors',
                                    operatingHours[day.key].enabled ? 'bg-muted/30' : ''
                                ]"
                            >
                                <div class="flex items-center gap-2.5 w-28">
                                    <Checkbox
                                        :id="`schedule-${day.key}`"
                                        :checked="operatingHours[day.key].enabled"
                                        @update:checked="operatingHours[day.key].enabled = $event === true"
                                        class="data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-500"
                                    />
                                    <Label
                                        :for="`schedule-${day.key}`"
                                        :class="[
                                            'cursor-pointer text-sm font-medium',
                                            operatingHours[day.key].enabled ? '' : 'text-muted-foreground'
                                        ]"
                                    >
                                        {{ day.label }}
                                    </Label>
                                </div>
                                <div v-if="operatingHours[day.key].enabled" class="flex items-center gap-2 flex-1">
                                    <Input
                                        v-model="operatingHours[day.key].start"
                                        type="time"
                                        class="w-28 h-9 text-sm font-mono"
                                    />
                                    <span class="text-muted-foreground text-sm font-medium">{{ __('to') }}</span>
                                    <Input
                                        v-model="operatingHours[day.key].end"
                                        type="time"
                                        class="w-28 h-9 text-sm font-mono"
                                    />
                                </div>
                                <div v-else class="flex-1">
                                    <Badge variant="outline" class="text-xs text-muted-foreground">
                                        {{ __('Closed') }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info when disabled -->
                    <div v-if="!scheduleEnabled" class="rounded-lg bg-muted/50 p-4 text-center">
                        <Clock class="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                        <p class="text-sm text-muted-foreground">
                            {{ __('Operating hours are disabled. Employees can scan attendance at any time.') }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="border-t pt-4">
                    <Button variant="outline" @click="closeScheduleDialog">
                        {{ __('Cancel') }}
                    </Button>
                    <Button @click="saveSchedule" :disabled="isSavingSchedule" class="min-w-24">
                        {{ isSavingSchedule ? __('Saving...') : __('Save Schedule') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

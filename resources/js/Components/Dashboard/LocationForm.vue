<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ButtonGroup } from '@/components/shared';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { GeofenceMap } from '@/components/shared/GeofenceMap';
import type { GeofenceType, GeofenceData } from '@/components/shared/GeofenceMap/types';
import type { InertiaForm } from '@inertiajs/vue3';
import {
    Circle,
    Hexagon,
    Users,
} from 'lucide-vue-next';

interface Employee {
    id: number;
    full_name: string;
}

interface School {
    id: number;
    name: string;
}

interface LocationFormData {
    school_id: number | null;
    name: string;
    code: string;
    description: string;
    type: string;
    address_line_1: string;
    address_line_2: string;
    city: string;
    state: string;
    country: string;
    postal_code: string;
    latitude: number | null;
    longitude: number | null;
    geofence_radius: number;
    geofence_type: GeofenceType;
    polygon_coordinates: [number, number][] | null;
    reference_employee_id: number | null;
    dynamic_radius: number;
    enforce_geofence: boolean;
    timezone: string;
    is_active: boolean;
}

interface Props {
    form: InertiaForm<LocationFormData>;
    mode?: 'create' | 'edit';
    types: Record<string, string>;
    employees?: Employee[];
    schools: School[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    employees: () => [],
});

// Common timezones
const timezones = [
    'Asia/Phnom_Penh',
    'Asia/Bangkok',
    'Asia/Ho_Chi_Minh',
    'Asia/Singapore',
    'Asia/Kuala_Lumpur',
    'Asia/Jakarta',
    'Asia/Manila',
    'Asia/Tokyo',
    'Asia/Seoul',
    'Asia/Shanghai',
    'Asia/Hong_Kong',
    'America/New_York',
    'America/Los_Angeles',
    'America/Chicago',
    'Europe/London',
    'Europe/Paris',
    'Australia/Sydney',
    'Pacific/Auckland',
];

// Computed for school select
const selectedSchool = computed({
    get: () => props.form.school_id?.toString(),
    set: (value: string | undefined) => {
        props.form.school_id = value ? parseInt(value) : null;
    },
});

// Computed for type select
const selectedType = computed({
    get: () => props.form.type || undefined,
    set: (value: string | undefined) => {
        props.form.type = value || 'office';
    },
});

// Computed for timezone select
const selectedTimezone = computed({
    get: () => props.form.timezone || undefined,
    set: (value: string | undefined) => {
        props.form.timezone = value || '';
    },
});

// Computed for reference employee select
const selectedEmployee = computed({
    get: () => props.form.reference_employee_id?.toString(),
    set: (value: string | undefined) => {
        props.form.reference_employee_id = value ? parseInt(value) : null;
    },
});

// Status switch
const isActive = computed({
    get: () => props.form.is_active,
    set: (value: boolean) => {
        props.form.is_active = value;
    },
});

// Enforce geofence switch
const enforceGeofence = computed({
    get: () => props.form.enforce_geofence,
    set: (value: boolean) => {
        props.form.enforce_geofence = value;
    },
});

// Description computed
const descriptionContent = computed({
    get: () => props.form.description ?? '',
    set: (val: string) => {
        props.form.description = val;
    },
});

const setGeofenceType = (type: GeofenceType) => {
    props.form.geofence_type = type;
};

const handleGeofenceChange = (geofence: GeofenceData) => {
    props.form.geofence_type = geofence.type;

    if (geofence.type === 'circle' && geofence.center) {
        props.form.latitude = geofence.center.lat;
        props.form.longitude = geofence.center.lng;
        props.form.geofence_radius = geofence.radius ?? 100;
        props.form.polygon_coordinates = null;
    } else if (geofence.type === 'polygon' && geofence.coordinates) {
        if (geofence.coordinates.length > 0) {
            const latSum = geofence.coordinates.reduce((sum, c) => sum + c[0], 0);
            const lngSum = geofence.coordinates.reduce((sum, c) => sum + c[1], 0);
            props.form.latitude = latSum / geofence.coordinates.length;
            props.form.longitude = lngSum / geofence.coordinates.length;
        }
        props.form.polygon_coordinates = geofence.coordinates;
    }
};
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column: Map & Geofence -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Geofence Type -->
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-muted-foreground">Geofence Type:</span>
                <ButtonGroup>
                    <Button
                        type="button"
                        :variant="form.geofence_type === 'circle' ? 'default' : 'outline'"
                        size="sm"
                        @click="setGeofenceType('circle')"
                    >
                        <Circle class="mr-1.5 h-4 w-4" />
                        Circle
                    </Button>
                    <Button
                        type="button"
                        :variant="form.geofence_type === 'polygon' ? 'default' : 'outline'"
                        size="sm"
                        @click="setGeofenceType('polygon')"
                    >
                        <Hexagon class="mr-1.5 h-4 w-4" />
                        Polygon
                    </Button>
                    <Button
                        type="button"
                        :variant="form.geofence_type === 'dynamic' ? 'default' : 'outline'"
                        size="sm"
                        @click="setGeofenceType('dynamic')"
                    >
                        <Users class="mr-1.5 h-4 w-4" />
                        Dynamic
                    </Button>
                </ButtonGroup>
            </div>

            <!-- Map -->
            <div v-if="form.geofence_type !== 'dynamic'">
                <GeofenceMap
                    :geofence-type="form.geofence_type"
                    :latitude="form.latitude"
                    :longitude="form.longitude"
                    :radius="form.geofence_radius"
                    :polygon-coordinates="form.polygon_coordinates"
                    height="700px"
                    @update:latitude="form.latitude = $event"
                    @update:longitude="form.longitude = $event"
                    @update:radius="form.geofence_radius = $event"
                    @update:polygon-coordinates="form.polygon_coordinates = $event"
                    @update:geofence-type="form.geofence_type = $event"
                    @geofence-change="handleGeofenceChange"
                />
                <div v-if="form.errors.latitude || form.errors.longitude" class="px-4 py-2 bg-destructive/10">
                    <p class="text-sm text-destructive">{{ form.errors.latitude || form.errors.longitude }}</p>
                </div>
            </div>

            <!-- Dynamic Geofence Card -->
            <Card v-if="form.geofence_type === 'dynamic'">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Dynamic Geofence Settings</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Employees must be within the radius of the selected supervisor's GPS location.
                    </p>
                    <div class="space-y-2">
                        <Label for="reference_employee">Reference Employee (Supervisor) <span class="text-destructive">*</span></Label>
                        <Select v-model="selectedEmployee">
                            <SelectTrigger id="reference_employee">
                                <SelectValue placeholder="Select supervisor" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="emp in employees"
                                    :key="emp.id"
                                    :value="emp.id.toString()"
                                >
                                    {{ emp.full_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.reference_employee_id" class="text-xs text-destructive">
                            {{ form.errors.reference_employee_id }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="dynamic_radius">Allowed Radius</Label>
                            <Badge variant="secondary">{{ form.dynamic_radius }}m</Badge>
                        </div>
                        <Input
                            id="dynamic_radius"
                            v-model.number="form.dynamic_radius"
                            type="range"
                            min="10"
                            max="500"
                            step="10"
                            class="w-full"
                        />
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span>10m</span>
                            <span>500m</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Address Card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-base">Address</CardTitle>
                        <Badge variant="secondary" class="text-xs">Optional</Badge>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="address_line_1">Address Line 1</Label>
                            <Input
                                id="address_line_1"
                                v-model="form.address_line_1"
                                placeholder="Street address"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="address_line_2">Address Line 2</Label>
                            <Input
                                id="address_line_2"
                                v-model="form.address_line_2"
                                placeholder="Building, floor, etc."
                            />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="city">City</Label>
                            <Input id="city" v-model="form.city" placeholder="City" />
                        </div>
                        <div class="space-y-2">
                            <Label for="state">State/Province</Label>
                            <Input id="state" v-model="form.state" placeholder="State" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="country">Country</Label>
                            <Input id="country" v-model="form.country" placeholder="Country" />
                        </div>
                        <div class="space-y-2">
                            <Label for="postal_code">Postal Code</Label>
                            <Input id="postal_code" v-model="form.postal_code" placeholder="Postal code" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Right Column: Basic Info, Settings -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Basic Information Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Basic Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="school_id">School <span class="text-destructive">*</span></Label>
                        <Select v-model="selectedSchool">
                            <SelectTrigger id="school_id">
                                <SelectValue placeholder="Select school" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="school in schools"
                                    :key="school.id"
                                    :value="school.id.toString()"
                                >
                                    {{ school.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.school_id" class="text-xs text-destructive">
                            {{ form.errors.school_id }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="name">Location Name <span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="Main Office"
                        />
                        <p v-if="form.errors.name" class="text-xs text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="code">Location Code</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            placeholder="HQ-001"
                        />
                        <p v-if="form.errors.code" class="text-xs text-destructive">
                            {{ form.errors.code }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="type">Location Type <span class="text-destructive">*</span></Label>
                        <Select v-model="selectedType">
                            <SelectTrigger id="type">
                                <SelectValue placeholder="Select type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(label, key) in types" :key="key" :value="key">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.type" class="text-xs text-destructive">
                            {{ form.errors.type }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <TiptapEditor
                            v-model="descriptionContent"
                            placeholder="Add description..."
                            min-height="100px"
                            max-height="200px"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Settings Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Settings</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="timezone">Timezone <span class="text-destructive">*</span></Label>
                        <Select v-model="selectedTimezone">
                            <SelectTrigger id="timezone">
                                <SelectValue placeholder="Select timezone" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="tz in timezones" :key="tz" :value="tz">
                                    {{ tz }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.timezone" class="text-xs text-destructive">
                            {{ form.errors.timezone }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Status & Enforcement Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Status & Enforcement</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Location Status</p>
                            <p class="text-xs text-muted-foreground">
                                {{ isActive ? 'Location is active' : 'Location is inactive' }}
                            </p>
                        </div>
                        <Switch v-model="isActive" />
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">{{ enforceGeofence ? 'Strict Mode' : 'Flexible Mode' }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ enforceGeofence ? 'Reject attendance outside zone' : 'Allow but flag violations' }}
                                </p>
                            </div>
                            <Switch v-model="enforceGeofence" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

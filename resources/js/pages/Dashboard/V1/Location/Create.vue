<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import LocationForm from '@employee/Components/Dashboard/LocationForm.vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';
import type { GeofenceType } from '@/components/shared/GeofenceMap/types';
import { ChevronLeft } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

interface Employee {
    id: number;
    full_name: string;
}

interface School {
    id: number;
    name: string;
}

interface Props {
    types: Record<string, string>;
    geofenceTypes: Record<string, string>;
    employees?: Employee[];
    schools: School[];
    defaultSchoolId: number | null;
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Attendance'), href: '/dashboard/attendances' },
    { title: __('Locations'), href: '/dashboard/locations' },
    { title: __('Create'), href: '/dashboard/locations/create' },
];

// Get user's timezone
const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

const form = useForm({
    school_id: props.defaultSchoolId,
    name: '',
    code: '',
    description: '',
    type: 'office',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    latitude: null as number | null,
    longitude: null as number | null,
    geofence_radius: 100,
    geofence_type: 'circle' as GeofenceType,
    polygon_coordinates: null as [number, number][] | null,
    reference_employee_id: null as number | null,
    dynamic_radius: 100,
    enforce_geofence: true,
    timezone: userTimezone,
    is_active: true,
});

const handleSubmit = () => {
    form.post('/dashboard/locations', {
        onSuccess: () => {
            toast.success(__('Location created successfully.'));
            router.visit('/dashboard/locations');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Create Location')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/dashboard/locations" class="text-muted-foreground hover:text-foreground">
                    <ChevronLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold">{{ __('Create Location') }}</h1>
                    <p class="text-sm text-muted-foreground">{{ __('Add a new geofence location for attendance tracking') }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <LocationForm
                    :form="form"
                    mode="create"
                    :types="types"
                    :employees="employees"
                    :schools="schools"
                />

                <!-- Actions at Bottom -->
                <div class="flex justify-end gap-3 pt-4">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/dashboard/locations">{{ __('Cancel') }}</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? __('Creating...') : __('Create Location') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

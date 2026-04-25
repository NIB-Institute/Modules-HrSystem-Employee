<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import LocationForm from '@employee/Components/Dashboard/LocationForm.vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';
import type { GeofenceType } from '@/components/shared/GeofenceMap/types';
import { ChevronLeft, Trash2 } from 'lucide-vue-next';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { useTranslation } from '@/composables/useTranslation';

interface Employee {
    id: number;
    full_name: string;
}

interface School {
    id: number;
    name: string;
}

interface Location {
    id: number;
    uuid: string;
    school_id: number;
    name: string;
    code: string | null;
    description: string | null;
    type: string;
    address_line_1: string | null;
    address_line_2: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    postal_code: string | null;
    latitude: number;
    longitude: number;
    geofence_radius: number;
    geofence_type: GeofenceType;
    polygon_coordinates: [number, number][] | null;
    reference_employee_id: number | null;
    dynamic_radius: number | null;
    enforce_geofence: boolean;
    timezone: string;
    operating_hours: Record<string, { start: string; end: string }> | null;
    is_active: boolean;
}

interface Props {
    locationData: Location;
    types: Record<string, string>;
    geofenceTypes: Record<string, string>;
    employees?: Employee[];
    schools: School[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Attendance'), href: '/dashboard/attendances' },
    { title: __('Locations'), href: '/dashboard/locations' },
    { title: __('Edit'), href: `/dashboard/locations/${props.locationData.uuid}/edit` },
];

const form = useForm({
    school_id: props.locationData.school_id,
    name: props.locationData.name,
    code: props.locationData.code || '',
    description: props.locationData.description || '',
    type: props.locationData.type,
    address_line_1: props.locationData.address_line_1 || '',
    address_line_2: props.locationData.address_line_2 || '',
    city: props.locationData.city || '',
    state: props.locationData.state || '',
    country: props.locationData.country || '',
    postal_code: props.locationData.postal_code || '',
    latitude: props.locationData.latitude != null ? Number(props.locationData.latitude) : null,
    longitude: props.locationData.longitude != null ? Number(props.locationData.longitude) : null,
    geofence_radius: props.locationData.geofence_radius,
    geofence_type: (props.locationData.geofence_type || 'circle') as GeofenceType,
    polygon_coordinates: props.locationData.polygon_coordinates,
    reference_employee_id: props.locationData.reference_employee_id,
    dynamic_radius: props.locationData.dynamic_radius || 100,
    enforce_geofence: props.locationData.enforce_geofence,
    timezone: props.locationData.timezone,
    is_active: props.locationData.is_active,
});

const handleSubmit = () => {
    form.put(`/dashboard/locations/${props.locationData.uuid}`, {
        onSuccess: () => {
            toast.success(__('Location updated successfully.'));
        },
    });
};

const handleDelete = () => {
    router.delete(`/dashboard/locations/${props.locationData.uuid}`, {
        onSuccess: () => {
            toast.success(__('Location deleted successfully.'));
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Edit') + ' ' + locationData.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/dashboard/locations" class="text-muted-foreground hover:text-foreground">
                        <ChevronLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-semibold">{{ __('Edit Location') }}</h1>
                        <p class="text-sm text-muted-foreground">{{ __('Update location:') }} {{ locationData.name }}</p>
                    </div>
                </div>
                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button variant="destructive" size="sm">
                            <Trash2 class="mr-2 h-4 w-4" />
                            {{ __('Delete') }}
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>{{ __('Delete Location') }}</AlertDialogTitle>
                            <AlertDialogDescription>
                                {{ __('Are you sure you want to delete') }} "{{ locationData.name }}"?
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
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <LocationForm
                    :form="form"
                    mode="edit"
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
                        {{ form.processing ? __('Saving...') : __('Save Changes') }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

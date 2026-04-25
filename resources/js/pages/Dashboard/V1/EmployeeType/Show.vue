<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Pencil, Tags, Users, Calendar, Clock } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { EmployeeTypeShowProps } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<EmployeeTypeShowProps>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employee Types'), href: '/dashboard/employee-types' },
    { title: props.employeeType.name, href: `/dashboard/employee-types/${props.employeeType.uuid}` },
];

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="employeeType.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/employee-types"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <h1 class="text-xl font-semibold">{{ __('Employee Type Details') }}</h1>
            </div>

            <!-- Main Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                            <Tags class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-2xl">{{ employeeType.name }}</CardTitle>
                            <div class="flex items-center gap-2 mt-1">
                                <Badge :variant="employeeType.status ? 'default' : 'secondary'">
                                    {{ employeeType.status ? __('Active') : __('Inactive') }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <Button as-child>
                        <Link :href="`/dashboard/employee-types/${employeeType.uuid}/edit`">
                            <Pencil class="h-4 w-4 mr-2" /> {{ __('Edit') }}
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="grid gap-6">
                    <!-- Description -->
                    <div>
                        <h3 class="text-sm font-medium text-muted-foreground mb-2">{{ __('Description') }}</h3>
                        <p class="text-sm">{{ employeeType.description || __('No description provided.') }}</p>
                    </div>

                    <!-- Working Hours -->
                    <div v-if="employeeType.time_start || employeeType.time_end">
                        <h3 class="text-sm font-medium text-muted-foreground mb-2">{{ __('Working Hours') }}</h3>
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <span class="text-sm">
                                {{ employeeType.time_start || '-' }} - {{ employeeType.time_end || '-' }}
                            </span>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="flex items-center gap-3 p-4 rounded-lg border">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                <Users class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">{{ __('Total Employees') }}</p>
                                <p class="text-lg font-semibold">{{ employeeType.employees_count || 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-lg border">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                <Calendar class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">{{ __('Created') }}</p>
                                <p class="text-sm font-medium">{{ formatDate(employeeType.created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-lg border">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                <Calendar class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">{{ __('Last Updated') }}</p>
                                <p class="text-sm font-medium">{{ formatDate(employeeType.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    ArrowLeft, Pencil, FileText, Calendar, Clock, User, CheckCircle,
    XCircle, AlertCircle, ClipboardCheck,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { PermissionRequestShowProps } from '@employee/types';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<PermissionRequestShowProps>();

const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Permission Requests'), href: '/dashboard/permission-requests' },
    { title: props.permissionRequest.type_label, href: `/dashboard/permission-requests/${props.permissionRequest.uuid}` },
];

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'approved':
            return 'default';
        case 'rejected':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved':
            return CheckCircle;
        case 'rejected':
            return XCircle;
        default:
            return AlertCircle;
    }
};

const getInitials = (name: string | null | undefined) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="permissionRequest.type_label" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/permission-requests"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <h1 class="text-xl font-semibold">{{ __('Permission Request Details') }}</h1>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Details -->
                <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                                <FileText class="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle class="text-xl">{{ permissionRequest.type_label }}</CardTitle>
                                <div class="flex items-center gap-2 mt-1">
                                    <Badge :variant="getStatusVariant(permissionRequest.status)">
                                        <component :is="getStatusIcon(permissionRequest.status)" class="h-3 w-3 mr-1" />
                                        {{ permissionRequest.status_label }}
                                    </Badge>
                                    <Badge variant="outline">{{ permissionRequest.total_days }} {{ __('day(s)') }}</Badge>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                v-if="permissionRequest.status === 'pending'"
                                as-child
                            >
                                <Link :href="`/dashboard/permission-requests/${permissionRequest.uuid}/review`">
                                    <ClipboardCheck class="h-4 w-4 mr-2" /> {{ __('Review') }}
                                </Link>
                            </Button>
                            <Button
                                v-if="permissionRequest.status === 'pending'"
                                variant="outline"
                                as-child
                            >
                                <Link :href="`/dashboard/permission-requests/${permissionRequest.uuid}/edit`">
                                    <Pencil class="h-4 w-4 mr-2" /> {{ __('Edit') }}
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Period -->
                        <div>
                            <h3 class="text-sm font-medium text-muted-foreground mb-2">{{ __('Period') }}</h3>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ formatDate(permissionRequest.from_date) }}</span>
                                </div>
                                <span class="text-muted-foreground">{{ __('to') }}</span>
                                <span>{{ formatDate(permissionRequest.to_date) }}</span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <h3 class="text-sm font-medium text-muted-foreground mb-2">{{ __('Reason') }}</h3>
                            <p class="text-sm whitespace-pre-wrap rounded-lg border bg-muted/30 p-4">
                                {{ permissionRequest.reason }}
                            </p>
                        </div>

                        <!-- Review Info (if reviewed) -->
                        <div v-if="permissionRequest.status !== 'pending'" class="border-t pt-6">
                            <h3 class="text-sm font-medium text-muted-foreground mb-4">{{ __('Review Information') }}</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ __('Reviewed by:') }} {{ permissionRequest.reviewer?.name || __('Unknown') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">{{ __('Reviewed at:') }} {{ permissionRequest.reviewed_at_formatted || '-' }}</span>
                                </div>
                                <div v-if="permissionRequest.review_note">
                                    <p class="text-sm font-medium mb-1">{{ __('Review Note:') }}</p>
                                    <p class="text-sm rounded-lg border bg-muted/30 p-3">
                                        {{ permissionRequest.review_note }}
                                    </p>
                                </div>
                                <div v-if="permissionRequest.rejected_status && permissionRequest.rejected_reason">
                                    <p class="text-sm font-medium mb-1 text-destructive">{{ __('Rejected Reason:') }}</p>
                                    <p class="text-sm rounded-lg border border-destructive/30 bg-destructive/10 p-3">
                                        {{ permissionRequest.rejected_reason }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Employee Info Sidebar -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('Employee') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col items-center gap-4 text-center">
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="permissionRequest.employee?.avatar_url || ''" />
                                <AvatarFallback class="text-lg">
                                    {{ getInitials(permissionRequest.employee?.full_name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="font-medium">{{ permissionRequest.employee?.full_name || '-' }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ permissionRequest.employee?.employee_code || '' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4 border-t pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">{{ __('Request Date') }}</span>
                                <span>{{ permissionRequest.request_date_formatted || '-' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">{{ __('Created') }}</span>
                                <span>{{ formatDate(permissionRequest.created_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

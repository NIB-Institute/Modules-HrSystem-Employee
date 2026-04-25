<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TrashTable, ButtonGroup } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Database, Trash2, RotateCcw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { TrashPaginationData, TrashConfigLocal, TrashConfig } from '@/types/trash';
import { useTranslation } from '@/composables/useTranslation';

interface Props {
    trashItems: TrashPaginationData;
    config: TrashConfig;
    filters: {
        search?: string;
        per_page?: number;
    };
}

const props = defineProps<Props>();

const { __ } = useTranslation();

const selectedUuids = ref<(string | number)[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Employee Types'), href: '/dashboard/employee-types' },
    { title: __('Trash'), href: '/dashboard/employee-types/trash' },
];

const trashConfig: TrashConfigLocal = {
    entityLabel: __('Employee Type'),
    entityLabelPlural: __('Employee Types'),
    restoreRoute: (uuid: string) => `/dashboard/employee-types/${uuid}/restore`,
    forceDeleteRoute: (uuid: string) => `/dashboard/employee-types/${uuid}/force-delete`,
    listRoute: '/dashboard/employee-types/trash',
};

const handleAll = () => {
    router.visit('/dashboard/employee-types');
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/employee-types/trash', {
        page,
        per_page: props.trashItems.meta?.per_page || 10,
        search: props.filters.search,
    }, { preserveState: true });
};

const handleSearch = (query: string) => {
    router.get('/dashboard/employee-types/trash', {
        search: query || undefined,
        per_page: props.trashItems.meta?.per_page || 10,
    }, { preserveState: true });
};

const handleBulkRestore = () => {
    router.put('/dashboard/employee-types/trash/bulk-restore', {
        uuids: selectedUuids.value,
    }, {
        preserveState: false,
        onSuccess: () => {
            selectedUuids.value = [];
        },
    });
};

const handleBulkForceDelete = () => {
    if (confirm(__('Are you sure you want to permanently delete') + ' ' + selectedUuids.value.length + ' ' + __('item(s)? This action cannot be undone.'))) {
        router.delete('/dashboard/employee-types/trash/bulk-force-delete', {
            data: { uuids: selectedUuids.value },
            preserveState: false,
            onSuccess: () => {
                selectedUuids.value = [];
            },
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Employee Types Trash')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ __('Employee Types Trash') }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Manage deleted employee types - restore or permanently delete') }}
                    </p>
                </div>
                <ButtonGroup>
                    <Button variant="outline" @click="handleAll">
                        <Database class="mr-2 h-4 w-4" />
                        {{ __('All') }}
                    </Button>
                    <Button variant="default">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ __('Trash') }}
                    </Button>
                </ButtonGroup>
            </div>

            <!-- Trash Table -->
            <TrashTable
                v-model:selected="selectedUuids"
                :items="trashItems.data"
                :config="trashConfig"
                :pagination="trashItems.meta"
                :show-type="false"
                :selectable="true"
                select-key="uuid"
                :empty-message="__('No deleted employee types found.')"
                empty-trash-route="/dashboard/employee-types/trash/empty"
                @page-change="handlePageChange"
                @search="handleSearch"
            >
                <template #bulk-actions>
                    <Button variant="outline" size="sm" @click="handleBulkRestore">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        {{ __('Restore Selected') }}
                    </Button>
                    <Button variant="destructive" size="sm" @click="handleBulkForceDelete">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ __('Delete Permanently') }}
                    </Button>
                </template>
            </TrashTable>
        </div>
    </AppLayout>
</template>

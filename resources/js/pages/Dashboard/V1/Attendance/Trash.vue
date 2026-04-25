<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TrashTable, ButtonGroup } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Database, Trash2, RotateCcw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { TrashPaginationData, TrashConfigLocal, TrashConfig } from '@/types/trash';

interface Props {
    trashItems: TrashPaginationData;
    config: TrashConfig;
    filters: {
        search?: string;
        per_page?: number;
    };
}

const props = defineProps<Props>();

const selectedUuids = ref<(string | number)[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendances', href: '/dashboard/attendances' },
    { title: 'Trash', href: '/dashboard/attendances/trash' },
];

const trashConfig: TrashConfigLocal = {
    entityLabel: 'Attendance',
    entityLabelPlural: 'Attendances',
    restoreRoute: (uuid: string) => `/dashboard/attendances/${uuid}/restore`,
    forceDeleteRoute: (uuid: string) => `/dashboard/attendances/${uuid}/force-delete`,
    listRoute: '/dashboard/attendances/trash',
};

const handleAll = () => {
    router.visit('/dashboard/attendances');
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/attendances/trash', {
        page,
        per_page: props.trashItems.meta?.per_page || 10,
        search: props.filters.search,
    }, { preserveState: true });
};

const handleSearch = (query: string) => {
    router.get('/dashboard/attendances/trash', {
        search: query || undefined,
        per_page: props.trashItems.meta?.per_page || 10,
    }, { preserveState: true });
};

const handleBulkRestore = () => {
    router.put('/dashboard/attendances/trash/bulk-restore', {
        uuids: selectedUuids.value,
    }, {
        preserveState: false,
        onSuccess: () => {
            selectedUuids.value = [];
        },
    });
};

const handleBulkForceDelete = () => {
    if (confirm(`Are you sure you want to permanently delete ${selectedUuids.value.length} item(s)? This action cannot be undone.`)) {
        router.delete('/dashboard/attendances/trash/bulk-force-delete', {
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
        <Head title="Attendances Trash" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Attendances Trash</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage deleted attendance records - restore or permanently delete
                    </p>
                </div>
                <ButtonGroup>
                    <Button variant="outline" @click="handleAll">
                        <Database class="mr-2 h-4 w-4" />
                        All
                    </Button>
                    <Button variant="default">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Trash
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
                empty-message="No deleted attendance records found."
                empty-trash-route="/dashboard/attendances/trash/empty"
                @page-change="handlePageChange"
                @search="handleSearch"
            >
                <template #bulk-actions>
                    <Button variant="outline" size="sm" @click="handleBulkRestore">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Restore Selected
                    </Button>
                    <Button variant="destructive" size="sm" @click="handleBulkForceDelete">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Permanently
                    </Button>
                </template>
            </TrashTable>
        </div>
    </AppLayout>
</template>

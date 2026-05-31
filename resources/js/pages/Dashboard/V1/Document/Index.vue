<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Plus, FileText, Database, Search, Download, Pencil, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useTranslation } from '@/composables/useTranslation';

interface DocumentModel {
    id: number;
    uuid: string;
    name: string;
    original_filename: string;
    mime_type: string;
    extension: string | null;
    size_bytes: number;
    human_size: string;
    description: string | null;
    url: string | null;
    uploader_name?: string | null;
    created_at: string;
}

interface Props {
    documents: {
        data: DocumentModel[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters: { search: string; extension: string };
    stats: { total: number; total_size_bytes: number };
}

const props = defineProps<Props>();
const { __ } = useTranslation();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('Dashboard'), href: '/dashboard' },
    { title: __('Documents'), href: '/dashboard/documents' },
];

const search = ref(props.filters.search || '');

const columns: TableColumn<DocumentModel>[] = [
    { key: 'name', label: __('Name'), render: (d) => d.name },
    { key: 'extension', label: __('Type'), render: (d) => (d.extension || '').toUpperCase() },
    { key: 'human_size', label: __('Size'), render: (d) => d.human_size },
    { key: 'uploader_name', label: __('Uploaded by'), render: (d) => d.uploader_name || '-' },
    { key: 'created_at', label: __('Uploaded at'), render: (d) => new Date(d.created_at).toLocaleString() },
];

const actions: TableAction<DocumentModel>[] = [
    {
        label: __('Download'),
        icon: Download,
        onClick: (d) => { window.location.href = `/dashboard/documents/${d.uuid}/download`; },
    },
    {
        label: __('Edit'),
        icon: Pencil,
        onClick: (d) => router.visit(`/dashboard/documents/${d.uuid}/edit`),
    },
    {
        label: __('Delete'),
        icon: Trash2,
        onClick: (d) => router.visit(`/dashboard/documents/${d.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.documents.meta.current_page,
    last_page: props.documents.meta.last_page,
    per_page: props.documents.meta.per_page,
    total: props.documents.meta.total,
}));

const getFilterParams = () => ({
    search: search.value || undefined,
});

const handleSearch = () => {
    router.get('/dashboard/documents', getFilterParams(), { preserveState: true });
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/documents', { page, per_page: pagination.value.per_page, ...getFilterParams() }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/documents', { per_page: perPage, ...getFilterParams() }, { preserveState: true });
};

const handleCreate = () => router.visit('/dashboard/documents/create');

const humanTotalSize = computed(() => {
    let b = props.stats.total_size_bytes;
    if (b < 1024) return `${b} B`;
    const u = ['KB', 'MB', 'GB', 'TB'];
    let i = -1;
    do { b /= 1024; i++; } while (b >= 1024 && i < u.length - 1);
    return `${b.toFixed(1)} ${u[i]}`;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('Documents')" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <StatsCard :title="__('Total Documents')" :value="props.stats.total" :icon="FileText" />
                <StatsCard :title="__('Storage Used')" :value="humanTotalSize" :icon="Database" variant="success" />
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('Documents') }}</h2>
                        <p class="text-sm text-muted-foreground">{{ __('Upload, manage and download files (PDF, Office, Images)') }}</p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('Upload Document') }}
                    </Button>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px] max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" :placeholder="__('Search by name, filename, description...')" class="pl-9" @keyup.enter="handleSearch" />
                    </div>
                </div>

                <TableReusable
                    :data="props.documents.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-name="{ item }">
                        <div class="flex items-center gap-2">
                            <FileText class="h-4 w-4 text-muted-foreground shrink-0" />
                            <div>
                                <div class="font-medium">{{ item.name }}</div>
                                <div class="text-xs text-muted-foreground truncate max-w-[300px]">{{ item.original_filename }}</div>
                            </div>
                        </div>
                    </template>
                    <template #cell-extension="{ item }">
                        <Badge variant="secondary">{{ (item.extension || '').toUpperCase() || '—' }}</Badge>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>

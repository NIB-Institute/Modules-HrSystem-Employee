<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowLeft, Printer, Download, FileDown, RefreshCw, User } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { onMounted, ref } from 'vue';
import QRCode from 'qrcode';

interface Props {
    employee: {
        id: number;
        uuid: string;
        employee_code: string;
        employee_qr_code: string;
        full_name: string;
        first_name: string;
        last_name: string;
        job_title: string | null;
        avatar_url: string | null;
        department_name: string | null;
        school_name: string | null;
        employee_type_name: string | null;
    };
    qrData: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Employees', href: '/dashboard/employees' },
    { title: props.employee.full_name, href: `/dashboard/employees/${props.employee.uuid}` },
    { title: 'QR Badge', href: `/dashboard/employees/${props.employee.uuid}/qr-badge` },
];

const qrCodeDataUrl = ref<string>('');
const qrCodeSvg = ref<string>('');
const isRegenerating = ref(false);

onMounted(async () => {
    await generateQrCode();
});

const generateQrCode = async () => {
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(props.qrData, {
            width: 200,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#ffffff',
            },
        });

        qrCodeSvg.value = await QRCode.toString(props.qrData, {
            type: 'svg',
            width: 200,
            margin: 1,
        });
    } catch (error) {
        console.error('Failed to generate QR code:', error);
    }
};

const handleBack = () => {
    router.visit(`/dashboard/employees/${props.employee.uuid}`);
};

const handleRegenerate = () => {
    isRegenerating.value = true;
    router.post(`/dashboard/employees/${props.employee.uuid}/regenerate-qr`, {}, {
        onFinish: () => {
            isRegenerating.value = false;
        },
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
        const scriptTag = '<' + 'script>window.onload = function() { window.print(); window.close(); }</' + 'script>';
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>ID Badge - ${props.employee.full_name}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        margin: 0;
                        padding: 20px;
                        box-sizing: border-box;
                    }
                    .badge {
                        width: 85.6mm;
                        height: 53.98mm;
                        border: 2px solid #000;
                        border-radius: 8px;
                        padding: 10px;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: row;
                        gap: 10px;
                    }
                    .badge-left {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    .badge-right {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .badge-right svg {
                        width: 120px;
                        height: 120px;
                    }
                    .company-name {
                        font-size: 10px;
                        color: #666;
                        margin-bottom: 5px;
                    }
                    .employee-name {
                        font-size: 14px;
                        font-weight: bold;
                        margin-bottom: 3px;
                    }
                    .job-title {
                        font-size: 11px;
                        color: #333;
                        margin-bottom: 3px;
                    }
                    .department {
                        font-size: 10px;
                        color: #666;
                        margin-bottom: 5px;
                    }
                    .employee-code {
                        font-size: 9px;
                        color: #999;
                        font-family: monospace;
                    }
                    @media print {
                        body { margin: 0; padding: 10mm; }
                    }
                </style>
            </head>
            <body>
                <div class="badge">
                    <div class="badge-left">
                        <div class="company-name">${props.employee.school_name || 'Company'}</div>
                        <div class="employee-name">${props.employee.full_name}</div>
                        <div class="job-title">${props.employee.job_title || ''}</div>
                        <div class="department">${props.employee.department_name || ''}</div>
                        <div class="employee-code">ID: ${props.employee.employee_code}</div>
                    </div>
                    <div class="badge-right">
                        ${qrCodeSvg.value}
                    </div>
                </div>
                ${scriptTag}
            </body>
            </html>
        `);
        printWindow.document.close();
    }
};

const handleDownload = () => {
    if (!qrCodeDataUrl.value) return;

    const link = document.createElement('a');
    link.download = `qr-badge-${props.employee.employee_code}.png`;
    link.href = qrCodeDataUrl.value;
    link.click();
};

const handleExportPdf = () => {
    const pdfWindow = window.open('', '_blank');
    if (pdfWindow) {
        pdfWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>ID Badge - ${props.employee.full_name}</title>
                <style>
                    @page {
                        size: 85.6mm 53.98mm;
                        margin: 0;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                    }
                    .badge {
                        width: 85.6mm;
                        height: 53.98mm;
                        border: 1px solid #ccc;
                        border-radius: 8px;
                        padding: 8px;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: row;
                        gap: 8px;
                    }
                    .badge-left {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }
                    .badge-right {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .badge-right img {
                        width: 100px;
                        height: 100px;
                    }
                    .company-name {
                        font-size: 9px;
                        color: #666;
                        margin-bottom: 4px;
                        text-transform: uppercase;
                    }
                    .employee-name {
                        font-size: 12px;
                        font-weight: bold;
                        margin-bottom: 2px;
                    }
                    .job-title {
                        font-size: 10px;
                        color: #333;
                        margin-bottom: 2px;
                    }
                    .department {
                        font-size: 9px;
                        color: #666;
                        margin-bottom: 4px;
                    }
                    .employee-code {
                        font-size: 8px;
                        color: #999;
                        font-family: monospace;
                    }
                </style>
            </head>
            <body>
                <div class="badge">
                    <div class="badge-left">
                        <div class="company-name">${props.employee.school_name || 'Company'}</div>
                        <div class="employee-name">${props.employee.full_name}</div>
                        <div class="job-title">${props.employee.job_title || ''}</div>
                        <div class="department">${props.employee.department_name || ''}</div>
                        <div class="employee-code">ID: ${props.employee.employee_code}</div>
                    </div>
                    <div class="badge-right">
                        <img src="${qrCodeDataUrl.value}" alt="QR Code" />
                    </div>
                </div>
            </body>
            </html>
        `);
        pdfWindow.document.close();

        setTimeout(() => {
            pdfWindow.print();
        }, 500);
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`QR Badge - ${employee.full_name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="handleBack">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold">Employee ID Badge</h1>
                        <p class="text-sm text-muted-foreground">
                            Print this QR badge for attendance scanning
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleRegenerate" :disabled="isRegenerating">
                        <RefreshCw class="mr-2 h-4 w-4" :class="{ 'animate-spin': isRegenerating }" />
                        Regenerate
                    </Button>
                    <Button variant="outline" @click="handleDownload">
                        <Download class="mr-2 h-4 w-4" />
                        PNG
                    </Button>
                    <Button variant="outline" @click="handleExportPdf">
                        <FileDown class="mr-2 h-4 w-4" />
                        PDF
                    </Button>
                    <Button @click="handlePrint">
                        <Printer class="mr-2 h-4 w-4" />
                        Print Badge
                    </Button>
                </div>
            </div>

            <!-- Badge Preview -->
            <div class="flex justify-center">
                <Card class="w-full max-w-lg">
                    <CardHeader class="text-center pb-2">
                        <CardTitle>Badge Preview</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col items-center space-y-6">
                        <!-- ID Badge Card -->
                        <div class="w-full max-w-md rounded-xl border-2 border-dashed p-4">
                            <div class="flex gap-4 rounded-lg border bg-linear-to-br from-slate-50 to-slate-100 p-4 shadow-sm">
                                <!-- Employee Info -->
                                <div class="flex flex-1 flex-col justify-center space-y-1">
                                    <p class="text-xs text-muted-foreground uppercase tracking-wider">
                                        {{ employee.school_name || 'Company' }}
                                    </p>
                                    <div class="flex items-center gap-3">
                                        <Avatar class="h-12 w-12">
                                            <AvatarImage :src="employee.avatar_url || ''" />
                                            <AvatarFallback>
                                                {{ getInitials(employee.full_name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <h3 class="font-bold">{{ employee.full_name }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ employee.job_title }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-muted-foreground">{{ employee.department_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">
                                        ID: {{ employee.employee_code }}
                                    </p>
                                </div>

                                <!-- QR Code -->
                                <div class="flex items-center justify-center rounded-lg bg-white p-2">
                                    <img
                                        v-if="qrCodeDataUrl"
                                        :src="qrCodeDataUrl"
                                        :alt="`QR Code for ${employee.full_name}`"
                                        class="h-28 w-28"
                                    />
                                    <div v-else class="flex h-28 w-28 items-center justify-center">
                                        <span class="text-xs text-muted-foreground">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="rounded-lg bg-muted p-4 text-center w-full">
                            <p class="text-sm font-medium">How to use this badge:</p>
                            <ol class="mt-2 text-sm text-muted-foreground text-left list-decimal list-inside space-y-1">
                                <li>Print this badge (credit card size: 85.6mm x 53.98mm)</li>
                                <li>Laminate for durability</li>
                                <li>Scan QR at checkpoint for attendance</li>
                            </ol>
                        </div>

                        <!-- QR Code Info -->
                        <div class="text-center text-sm text-muted-foreground space-y-1">
                            <p>QR Code: <span class="font-mono">{{ employee.employee_qr_code }}</span></p>
                            <p v-if="employee.employee_type_name">Type: {{ employee.employee_type_name }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Users, Search, Shield, Building2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

interface EmployeeOption {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    avatar_url: string | null;
    department_name: string | null;
}

const props = defineProps<{
    employees: EmployeeOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Self Service', href: '/dashboard/attendances/self-service' },
    { title: 'Select Employee', href: '#' },
];

const search = ref('');

const filteredEmployees = computed(() => {
    if (!search.value) return props.employees;

    const term = search.value.toLowerCase();
    return props.employees.filter(
        (e) =>
            e.full_name.toLowerCase().includes(term) ||
            e.employee_code.toLowerCase().includes(term) ||
            e.department_name?.toLowerCase().includes(term)
    );
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const selectEmployee = (employeeId: number) => {
    router.visit(`/dashboard/attendances/self-service?employee_id=${employeeId}`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Select Employee - Self Service" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Admin Mode Banner -->
            <div class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
                <Shield class="h-5 w-5 text-amber-600" />
                <div>
                    <p class="font-medium text-amber-800 dark:text-amber-200">Admin Mode</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        You are not linked to an employee. Select an employee below to test the self-service feature.
                    </p>
                </div>
            </div>

            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold">Select Employee</h1>
                <p class="mt-2 text-muted-foreground">
                    Choose an employee to view their self-service attendance page
                </p>
            </div>

            <!-- Search -->
            <div class="relative max-w-md mx-auto w-full">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    placeholder="Search by name, code, or department..."
                    class="pl-10"
                />
            </div>

            <!-- Employee Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card
                    v-for="employee in filteredEmployees"
                    :key="employee.id"
                    class="cursor-pointer transition-all hover:border-primary hover:shadow-md"
                    @click="selectEmployee(employee.id)"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <Avatar class="h-12 w-12">
                                <AvatarImage :src="employee.avatar_url ?? ''" :alt="employee.full_name" />
                                <AvatarFallback>{{ getInitials(employee.full_name) }}</AvatarFallback>
                            </Avatar>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ employee.full_name }}</p>
                                <p class="text-sm text-muted-foreground">{{ employee.employee_code }}</p>
                                <div v-if="employee.department_name" class="flex items-center gap-1 mt-1">
                                    <Building2 class="h-3 w-3 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground truncate">
                                        {{ employee.department_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="filteredEmployees.length === 0" class="py-12 text-center">
                <Users class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
                <p class="mt-4 text-muted-foreground">No employees found matching your search.</p>
            </div>

            <!-- Count -->
            <div class="text-center text-sm text-muted-foreground">
                Showing {{ filteredEmployees.length }} of {{ employees.length }} employees
            </div>
        </div>
    </AppLayout>
</template>

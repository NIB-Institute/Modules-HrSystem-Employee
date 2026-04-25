<script setup lang="ts">
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Eye, EyeOff } from 'lucide-vue-next';
import SearchableSelect from '@/components/shared/SearchableSelect/SearchableSelect.vue';
import type { InertiaForm } from '@inertiajs/vue3';

interface EmployeeUser {
    id: number;
    name: string;
    email: string;
}

interface EmployeeData {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    email: string | null;
    avatar_url: string | null;
    user: EmployeeUser | null;
}

interface EmployeeOption {
    value: string;
    label: string;
    description: string;
    avatar_url: string | null;
}

interface FormData {
    password: string;
    password_confirmation: string;
}

const props = defineProps<{
    employee: EmployeeData;
    employeeOptions: EmployeeOption[];
    form: InertiaForm<FormData>;
    selectedEmployeeUuid: string | number | null;
}>();

const emit = defineEmits<{
    (e: 'update:selectedEmployeeUuid', value: string | number | null): void;
}>();

// Local state for password visibility
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const getInitials = (name: string) => {
    return name.split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2);
};

// Handle employee selection change
const handleEmployeeChange = (value: string | number | null) => {
    emit('update:selectedEmployeeUuid', value);
};
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Employee Info Card -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Employee Search Select -->
            <div class="space-y-2">
                <Label>Select Employee</Label>
                <SearchableSelect
                    :model-value="selectedEmployeeUuid"
                    :options="employeeOptions"
                    placeholder="Search employee..."
                    search-placeholder="Type to search..."
                    empty-message="No employees with accounts found."
                    @update:model-value="handleEmployeeChange"
                />
            </div>

            <div class="flex flex-col items-center text-center pt-4 border-t">
                <Avatar class="h-20 w-20 mb-4">
                    <AvatarImage :src="employee.avatar_url || ''" :alt="employee.full_name" />
                    <AvatarFallback class="text-xl font-semibold bg-primary text-primary-foreground">
                        {{ getInitials(employee.full_name) }}
                    </AvatarFallback>
                </Avatar>
                <h3 class="font-semibold text-lg">{{ employee.full_name }}</h3>
                <p class="text-sm text-muted-foreground font-mono">{{ employee.employee_code }}</p>
                <div v-if="employee.user" class="mt-4 w-full space-y-2 text-sm">
                    <div class="flex justify-between py-2 border-t">
                        <span class="text-muted-foreground">Account Name</span>
                        <span class="font-medium">{{ employee.user.name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t">
                        <span class="text-muted-foreground">Login Email</span>
                        <span class="font-medium">{{ employee.user.email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Fields -->
        <div class="lg:col-span-2 space-y-4">
            <div class="space-y-2">
                <Label for="password">New Password <span class="text-destructive">*</span></Label>
                <div class="relative">
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Minimum 8 characters"
                        autocomplete="new-password"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        @click="showPassword = !showPassword"
                    >
                        <Eye v-if="!showPassword" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-xs text-destructive">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation">Confirm Password <span class="text-destructive">*</span></Label>
                <div class="relative">
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        placeholder="Repeat password"
                        autocomplete="new-password"
                        class="pr-10"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                    >
                        <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

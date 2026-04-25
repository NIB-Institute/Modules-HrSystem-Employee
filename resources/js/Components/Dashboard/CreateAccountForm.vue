<script setup lang="ts">
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Eye, EyeOff, Mail, Phone } from 'lucide-vue-next';
import SearchableSelect from '@/components/shared/SearchableSelect/SearchableSelect.vue';
import type { InertiaForm } from '@inertiajs/vue3';

interface EmployeeData {
    id: number;
    uuid: string;
    full_name: string;
    employee_code: string;
    email: string | null;
    phone_number: string | null;
    avatar_url: string | null;
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
    login_method: string;
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

// Computed properties
const hasEmail = computed(() => !!props.employee.email);
const hasPhone = computed(() => !!props.employee.phone_number);
const hasBothOptions = computed(() => hasEmail.value && hasPhone.value);

// Get the login identifier based on selected method
const loginIdentifier = computed(() => {
    if (props.form.login_method === 'email') {
        return props.employee.email;
    }
    return props.employee.phone_number;
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Handle employee selection change
const handleEmployeeChange = (value: string | number | null) => {
    emit('update:selectedEmployeeUuid', value);
};

// Handle login method selection
const selectLoginMethod = (method: 'email' | 'phone') => {
    props.form.login_method = method;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Employee Search Select -->
        <div class="space-y-2">
            <Label>Select Employee</Label>
            <SearchableSelect
                :model-value="selectedEmployeeUuid"
                :options="employeeOptions"
                placeholder="Search employee..."
                search-placeholder="Type to search..."
                empty-message="No employees without accounts found."
                @update:model-value="handleEmployeeChange"
            />
        </div>

        <!-- Employee Info -->
        <div class="flex items-center gap-4 p-4 bg-muted/50 rounded-lg">
            <Avatar class="h-12 w-12">
                <AvatarImage :src="employee.avatar_url || ''" :alt="employee.full_name" />
                <AvatarFallback class="bg-primary text-primary-foreground">
                    {{ getInitials(employee.full_name) }}
                </AvatarFallback>
            </Avatar>
            <div class="flex-1">
                <p class="font-medium">{{ employee.full_name }}</p>
                <p class="text-sm text-muted-foreground">{{ employee.employee_code }}</p>
                <div class="flex flex-col gap-1 mt-1">
                    <p v-if="employee.email" class="text-sm text-muted-foreground flex items-center gap-1">
                        <Mail class="h-3 w-3" /> {{ employee.email }}
                    </p>
                    <p v-if="employee.phone_number" class="text-sm text-muted-foreground flex items-center gap-1">
                        <Phone class="h-3 w-3" /> {{ employee.phone_number }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Login Method Selection (only show if both are available) -->
        <div v-if="hasBothOptions" class="space-y-3">
            <Label>Login Method</Label>
            <div class="grid grid-cols-2 gap-4">
                <button
                    type="button"
                    class="flex flex-col items-center justify-between rounded-md border-2 bg-popover p-4 hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors"
                    :class="form.login_method === 'email' ? 'border-primary' : 'border-muted'"
                    @click="selectLoginMethod('email')"
                >
                    <Mail class="mb-2 h-6 w-6" />
                    <span class="text-sm font-medium">Email</span>
                    <span class="text-xs text-muted-foreground truncate max-w-full">{{ employee.email }}</span>
                </button>
                <button
                    type="button"
                    class="flex flex-col items-center justify-between rounded-md border-2 bg-popover p-4 hover:bg-accent hover:text-accent-foreground cursor-pointer transition-colors"
                    :class="form.login_method === 'phone' ? 'border-primary' : 'border-muted'"
                    @click="selectLoginMethod('phone')"
                >
                    <Phone class="mb-2 h-6 w-6" />
                    <span class="text-sm font-medium">Phone</span>
                    <span class="text-xs text-muted-foreground">{{ employee.phone_number }}</span>
                </button>
            </div>
            <p class="text-xs text-muted-foreground">
                Employee will use <strong>{{ loginIdentifier }}</strong> to log in.
            </p>
        </div>

        <!-- Show info if only one option available -->
        <div v-else class="p-3 bg-muted/50 rounded-lg">
            <p class="text-sm text-muted-foreground">
                <span v-if="hasEmail">Employee will use email <strong>{{ employee.email }}</strong> to log in.</span>
                <span v-else-if="hasPhone">Employee will use phone <strong>{{ employee.phone_number }}</strong> to log in.</span>
            </p>
        </div>

        <!-- Password Fields -->
        <div class="space-y-4">
            <div class="space-y-2">
                <Label for="password">Password <span class="text-destructive">*</span></Label>
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

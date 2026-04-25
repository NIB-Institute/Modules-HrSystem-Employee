<script setup lang="ts">
import { computed } from 'vue';
import { useTranslation } from '@/composables/useTranslation';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { User, Mail, Phone, Calendar, MapPin, Heart } from 'lucide-vue-next';
import type { InertiaForm } from '@inertiajs/vue3';
import type { EmployeeFormData, MaritalStatusOption } from '../../../../types';

interface Props {
    form: InertiaForm<EmployeeFormData>;
    mode?: 'create' | 'edit';
    maritalStatuses?: MaritalStatusOption[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    maritalStatuses: () => [
        { value: 'single', label: 'Single' },
        { value: 'married', label: 'Married' },
    ],
});

const { __ } = useTranslation();

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
];

const selectedGender = computed({
    get: () => props.form.gender || undefined,
    set: (value: string | undefined) => {
        props.form.gender = (value as 'male' | 'female' | 'other') || null;
    },
});

const selectedMaritalStatus = computed({
    get: () => props.form.marital_status || undefined,
    set: (value: string | undefined) => {
        props.form.marital_status = (value as 'single' | 'married') || null;
        if (value === 'single') {
            props.form.family_members = props.form.family_members.filter(
                (m) => m.relationship !== 'spouse'
            );
        }
    },
});
</script>

<template>
    <Card>
        <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-2 text-base">
                <User class="h-4 w-4 text-primary" />
                {{ __('Basic Information') }}
            </CardTitle>
            <CardDescription>{{ __('Personal details and contact information') }}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Identity Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <User class="h-4 w-4" />
                    <span>{{ __('Identity') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 rounded-lg border bg-muted/30 p-4">
                    <!-- Employee Code -->
                    <div class="space-y-2">
                        <Label for="employee_code" class="text-xs font-medium">{{ __('Employee Code') }} <span class="text-destructive">*</span></Label>
                        <Input
                            id="employee_code"
                            v-model="form.employee_code"
                            type="text"
                            placeholder="EMP-000001"
                            :disabled="mode === 'edit'"
                            class="bg-background"
                        />
                        <p v-if="form.errors.employee_code" class="text-xs text-destructive">
                            {{ form.errors.employee_code }}
                        </p>
                    </div>

                    <!-- First Name -->
                    <div class="space-y-2">
                        <Label for="first_name" class="text-xs font-medium">{{ __('First Name') }} <span class="text-destructive">*</span></Label>
                        <Input id="first_name" v-model="form.first_name" type="text" placeholder="John" class="bg-background" />
                        <p v-if="form.errors.first_name" class="text-xs text-destructive">
                            {{ form.errors.first_name }}
                        </p>
                    </div>

                    <!-- Last Name -->
                    <div class="space-y-2">
                        <Label for="last_name" class="text-xs font-medium">{{ __('Last Name') }} <span class="text-destructive">*</span></Label>
                        <Input id="last_name" v-model="form.last_name" type="text" placeholder="Doe" class="bg-background" />
                        <p v-if="form.errors.last_name" class="text-xs text-destructive">
                            {{ form.errors.last_name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Mail class="h-4 w-4" />
                    <span>{{ __('Contact') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 rounded-lg border bg-muted/30 p-4">
                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-xs font-medium">{{ __('Email') }}</Label>
                        <div class="relative">
                            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input id="email" v-model="form.email" type="email" placeholder="john.doe@example.com" class="pl-10 bg-background" />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Phone Number -->
                    <div class="space-y-2">
                        <Label for="phone_number" class="text-xs font-medium">{{ __('Phone Number') }}</Label>
                        <div class="relative">
                            <Phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input id="phone_number" v-model="form.phone_number" type="text" placeholder="+1 234 567 8900" class="pl-10 bg-background" />
                        </div>
                        <p v-if="form.errors.phone_number" class="text-xs text-destructive">
                            {{ form.errors.phone_number }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Personal Details Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Heart class="h-4 w-4" />
                    <span>{{ __('Personal Details') }}</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 rounded-lg border bg-muted/30 p-4">
                    <!-- Gender -->
                    <div class="space-y-2">
                        <Label for="gender" class="text-xs font-medium">{{ __('Gender') }}</Label>
                        <Select v-model="selectedGender">
                            <SelectTrigger id="gender" class="bg-background">
                                <SelectValue :placeholder="__('Select gender')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in genderOptions" :key="opt.value" :value="opt.value">
                                    {{ __(opt.label) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.gender" class="text-xs text-destructive">
                            {{ form.errors.gender }}
                        </p>
                    </div>

                    <!-- Date of Birth -->
                    <div class="space-y-2">
                        <Label for="date_of_birth" class="text-xs font-medium">{{ __('Date of Birth') }}</Label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                            <Input id="date_of_birth" v-model="form.date_of_birth" type="date" class="pl-10 bg-background" />
                        </div>
                        <p v-if="form.errors.date_of_birth" class="text-xs text-destructive">
                            {{ form.errors.date_of_birth }}
                        </p>
                    </div>

                    <!-- Birth Place -->
                    <div class="space-y-2">
                        <Label for="birth_place" class="text-xs font-medium">{{ __('Birth Place') }}</Label>
                        <Input id="birth_place" v-model="form.birth_place" type="text" placeholder="City, Country" class="bg-background" />
                        <p v-if="form.errors.birth_place" class="text-xs text-destructive">
                            {{ form.errors.birth_place }}
                        </p>
                    </div>

                    <!-- Marital Status -->
                    <div class="space-y-2">
                        <Label for="marital_status" class="text-xs font-medium">{{ __('Marital Status') }}</Label>
                        <Select v-model="selectedMaritalStatus">
                            <SelectTrigger id="marital_status" class="bg-background">
                                <SelectValue :placeholder="__('Select status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="opt in maritalStatuses" :key="opt.value" :value="opt.value">
                                    {{ __(opt.label) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.marital_status" class="text-xs text-destructive">
                            {{ form.errors.marital_status }}
                        </p>
                    </div>

                    <!-- Ethnicity -->
                    <div class="space-y-2">
                        <Label for="ethnicity" class="text-xs font-medium">{{ __('Ethnicity') }}</Label>
                        <Input id="ethnicity" v-model="form.ethnicity" type="text" :placeholder="__('Ethnicity')" class="bg-background" />
                        <p v-if="form.errors.ethnicity" class="text-xs text-destructive">
                            {{ form.errors.ethnicity }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Address Section -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span>{{ __('Address') }}</span>
                </div>
                <div class="rounded-lg border bg-muted/30 p-4">
                    <div class="space-y-2">
                        <Label for="current_address" class="text-xs font-medium">{{ __('Current Address') }}</Label>
                        <TiptapEditor
                            v-model="form.current_address"
                            placeholder="Street address, City, State, ZIP Code..."
                            min-height="100px"
                            max-height="250px"
                        />
                        <p v-if="form.errors.current_address" class="text-xs text-destructive">
                            {{ form.errors.current_address }}
                        </p>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

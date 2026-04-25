<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Switch } from '@/components/ui/switch';
import type { EmployeeTypeFormData } from '@employee/types';

interface Props {
    form: {
        name: string;
        description: string;
        time_start: string;
        time_end: string;
        status: boolean;
        errors: Record<string, string>;
    } & EmployeeTypeFormData;
    mode: 'create' | 'edit';
}

const props = defineProps<Props>();

const isActive = computed({
    get: () => props.form.status,
    set: (value: boolean) => {
        props.form.status = value;
    },
});
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column: Form Fields -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Basic Information Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Type Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">
                                Name <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="name"
                                v-model="props.form.name"
                                placeholder="Enter type name"
                                :class="{ 'border-destructive': props.form.errors.name }"
                            />
                            <p v-if="props.form.errors.name" class="text-xs text-destructive">
                                {{ props.form.errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <TiptapEditor
                                v-model="props.form.description"
                                placeholder="Enter type description..."
                                min-height="250px"
                                max-height="400px"
                            />
                            <p v-if="props.form.errors.description" class="text-xs text-destructive">
                                {{ props.form.errors.description }}
                            </p>
                        </div>

                        <!-- Time Range -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="time_start">Start Time</Label>
                                <Input
                                    id="time_start"
                                    type="time"
                                    v-model="props.form.time_start"
                                    :class="{ 'border-destructive': props.form.errors.time_start }"
                                />
                                <p v-if="props.form.errors.time_start" class="text-xs text-destructive">
                                    {{ props.form.errors.time_start }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="time_end">End Time</Label>
                                <Input
                                    id="time_end"
                                    type="time"
                                    v-model="props.form.time_end"
                                    :class="{ 'border-destructive': props.form.errors.time_end }"
                                />
                                <p v-if="props.form.errors.time_end" class="text-xs text-destructive">
                                    {{ props.form.errors.time_end }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Right Column: Status -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Status Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">Active Status</p>
                            <p class="text-xs text-muted-foreground">
                                {{ isActive ? 'Type is active' : 'Type is inactive' }}
                            </p>
                        </div>
                        <Switch v-model="isActive" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

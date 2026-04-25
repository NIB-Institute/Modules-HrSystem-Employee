<script setup lang="ts">
import { computed } from 'vue';
import { useTranslation } from '@/composables/useTranslation';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { ImageUpload } from '@/components/shared';
import { Camera, Activity, UserCog, FileImage } from 'lucide-vue-next';
import type { InertiaForm } from '@inertiajs/vue3';
import type { EmployeeFormData } from '../../../../types';

interface Props {
    form: InertiaForm<EmployeeFormData>;
    mode?: 'create' | 'edit';
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
});

const { __ } = useTranslation();

// Avatar images
const avatarImages = computed({
    get: () => props.form.avatar_url ? [props.form.avatar_url] : [],
    set: (value: string[]) => {
        props.form.avatar_url = value.length > 0 ? value[0] : '';
    },
});

// Certificate images (supports multiple)
const certificateImages = computed({
    get: () => {
        // Handle both array and single string for backwards compatibility
        if (Array.isArray(props.form.certificate_images)) {
            return props.form.certificate_images;
        }
        // Fallback for old single image field
        if (props.form.certificate_image) {
            return [props.form.certificate_image];
        }
        return [];
    },
    set: (value: string[]) => {
        props.form.certificate_images = value;
        // Keep single field updated for backwards compatibility
        props.form.certificate_image = value.length > 0 ? value[0] : '';
    },
});

// Status
const isActive = computed({
    get: () => props.form.status,
    set: (value: boolean) => {
        props.form.status = value;
    },
});

// Create account
const createAccount = computed({
    get: () => props.form.create_account ?? false,
    set: (value: boolean) => {
        props.form.create_account = value;
        if (!value) {
            props.form.password = '';
            props.form.password_confirmation = '';
        }
    },
});
</script>

<template>
    <Card>
        <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-2 text-base">
                <Camera class="h-4 w-4 text-primary" />
                {{ __('Profile & Settings') }}
            </CardTitle>
            <CardDescription>{{ __('Photo, status, and account configuration') }}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
            <!-- Profile Photo Section -->
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Camera class="h-4 w-4" />
                    <span>{{ __('Profile Photo') }}</span>
                </div>
                <ImageUpload
                    v-model="avatarImages"
                    label=""
                    :multiple="false"
                    :max-files="1"
                    :max-size="5"
                    :error="form.errors.avatar_url"
                />
            </div>

            <!-- Status Section -->
            <div class="space-y-3 border-t pt-6">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <Activity class="h-4 w-4" />
                    <span>{{ __('Status') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg border bg-muted/30 p-3">
                    <div>
                        <p class="text-sm font-medium">{{ __('Employee Status') }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ isActive ? __('Active') : __('Inactive') }}
                        </p>
                    </div>
                    <Switch v-model="isActive" />
                </div>
            </div>

            <!-- Account Settings Section (Create mode only) -->
            <div v-if="mode === 'create'" class="space-y-3 border-t pt-6">
                <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                    <UserCog class="h-4 w-4" />
                    <span>{{ __('Account Settings') }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg border bg-muted/30 p-3">
                    <div>
                        <p class="text-sm font-medium">{{ __('Create Login Account') }}</p>
                        <p class="text-xs text-muted-foreground">{{ __('Allow employee to log in') }}</p>
                    </div>
                    <Switch v-model="createAccount" />
                </div>
                <div v-if="createAccount" class="space-y-3 rounded-lg border bg-muted/30 p-3">
                    <div class="space-y-2">
                        <Label for="password" class="text-xs font-medium">{{ __('Password') }} <span class="text-destructive">*</span></Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :placeholder="__('Minimum 8 characters')"
                            autocomplete="new-password"
                            class="bg-background"
                        />
                        <p v-if="form.errors.password" class="text-xs text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-xs font-medium">{{ __('Confirm Password') }} <span class="text-destructive">*</span></Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            :placeholder="__('Repeat password')"
                            autocomplete="new-password"
                            class="bg-background"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">{{ __('Employee will use their email to log in.') }}</p>
                </div>
            </div>

            <!-- Certificate Documents Section -->
            <div class="space-y-3 border-t pt-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <FileImage class="h-4 w-4" />
                        <span>{{ __('Certificate Documents') }}</span>
                    </div>
                    <span class="text-xs text-muted-foreground">{{ __('Max 10 files') }}</span>
                </div>
                <ImageUpload
                    v-model="certificateImages"
                    label=""
                    :multiple="true"
                    :max-files="10"
                    :max-size="5"
                    :error="form.errors.certificate_images || form.errors.certificate_image"
                />
                <p class="text-xs text-muted-foreground">{{ __('Upload certificates, degrees, or qualification documents') }}</p>
            </div>
        </CardContent>
    </Card>
</template>

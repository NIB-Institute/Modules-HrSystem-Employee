<script setup lang="ts">
import { useTranslation } from '@/composables/useTranslation';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Award, FileText, Hash, Upload, X, Image } from 'lucide-vue-next';
import type { InertiaForm } from '@inertiajs/vue3';
import type { EmployeeFormData } from '../../../../types';
import { ref, computed } from 'vue';

interface Props {
    form: InertiaForm<EmployeeFormData>;
}

const props = defineProps<Props>();

const { __ } = useTranslation();

// Preview for newly selected files
const newFilePreviews = ref<string[]>([]);

// Handle file selection
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;

    if (!files) return;

    // Convert FileList to array and add to form
    const fileArray = Array.from(files);
    if (!props.form.certificate_images) {
        props.form.certificate_images = [];
    }

    // Create previews for new files
    fileArray.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            newFilePreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });

    // Add files to form (Inertia will handle File objects)
    props.form.certificate_images = [...(props.form.certificate_images || []), ...fileArray] as any;

    // Reset input
    target.value = '';
};

// Remove a preview (new file)
const removeNewPreview = (index: number) => {
    newFilePreviews.value.splice(index, 1);
    if (Array.isArray(props.form.certificate_images)) {
        props.form.certificate_images.splice(index, 1);
    }
};

// Check if there are any images (existing or new)
const hasImages = computed(() => {
    return newFilePreviews.value.length > 0;
});
</script>

<template>
    <Card>
        <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-2 text-base">
                <Award class="h-4 w-4 text-primary" />
                {{ __('Certification') }}
            </CardTitle>
            <CardDescription>{{ __('Educational qualifications and credentials') }}</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 rounded-lg border bg-muted/30 p-4">
                <div class="space-y-2">
                    <Label for="certificate" class="text-xs font-medium">{{ __('Certificate Name') }}</Label>
                    <div class="relative">
                        <FileText class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input id="certificate" v-model="form.certificate" type="text" :placeholder="__('Bachelor\'s Degree')" class="pl-10 bg-background" />
                    </div>
                    <p v-if="form.errors.certificate" class="text-xs text-destructive">
                        {{ form.errors.certificate }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="certificate_code" class="text-xs font-medium">{{ __('Certificate Code') }}</Label>
                    <div class="relative">
                        <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input id="certificate_code" v-model="form.certificate_code" type="text" placeholder="CERT-2024-001" class="pl-10 bg-background" />
                    </div>
                    <p v-if="form.errors.certificate_code" class="text-xs text-destructive">
                        {{ form.errors.certificate_code }}
                    </p>
                </div>
            </div>

            <!-- Certificate Images Upload -->
            <div class="space-y-3">
                <Label class="text-xs font-medium">{{ __('Certificate Images') }}</Label>

                <!-- Upload Button -->
                <div class="flex items-center gap-4">
                    <label
                        class="flex items-center gap-2 px-4 py-2 rounded-md border border-dashed border-muted-foreground/50 bg-muted/30 hover:bg-muted cursor-pointer transition-colors"
                    >
                        <Upload class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">{{ __('Upload Images') }}</span>
                        <input
                            type="file"
                            accept="image/*"
                            multiple
                            class="hidden"
                            @change="handleFileSelect"
                        />
                    </label>
                    <span class="text-xs text-muted-foreground">{{ __('Supports: JPG, PNG, GIF') }}</span>
                </div>

                <!-- Image Previews -->
                <div v-if="hasImages" class="flex flex-wrap gap-3">
                    <!-- New file previews -->
                    <div
                        v-for="(preview, index) in newFilePreviews"
                        :key="`new-${index}`"
                        class="relative group"
                    >
                        <img
                            :src="preview"
                            :alt="`Certificate ${index + 1}`"
                            class="h-24 w-24 object-cover rounded-lg border shadow-sm"
                        />
                        <Button
                            type="button"
                            variant="destructive"
                            size="icon"
                            class="absolute -top-2 -right-2 h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
                            @click="removeNewPreview(index)"
                        >
                            <X class="h-3 w-3" />
                        </Button>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="flex items-center gap-2 text-muted-foreground">
                    <Image class="h-4 w-4" />
                    <span class="text-sm">{{ __('No certificate images uploaded') }}</span>
                </div>

                <p v-if="form.errors.certificate_images" class="text-xs text-destructive">
                    {{ form.errors.certificate_images }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>

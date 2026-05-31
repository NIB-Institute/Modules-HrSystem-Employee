<script setup lang="ts">
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import TiptapEditor from '@/components/TiptapEditor.vue';
import { Upload, FileText, X } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

export interface DocumentFormShape {
    name: string;
    description: string;
    file: File | null;
    errors: Record<string, string>;
    processing: boolean;
    progress?: { percentage?: number } | null;
}

export interface ExistingDocument {
    original_filename: string;
    human_size: string;
    extension: string | null;
}

interface Props {
    form: DocumentFormShape;
    mode: 'create' | 'edit';
    document?: ExistingDocument | null;
}

const props = withDefaults(defineProps<Props>(), {
    document: null,
});

const { __ } = useTranslation();

const fileInput = ref<HTMLInputElement | null>(null);
const acceptedMimes = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png';

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const f = target.files?.[0] ?? null;
    props.form.file = f;
    if (props.mode === 'create' && f && !props.form.name.trim()) {
        props.form.name = f.name.replace(/\.[^.]+$/, '');
    }
};

const removeFile = () => {
    props.form.file = null;
    if (fileInput.value) fileInput.value.value = '';
};

const humanSize = (b: number): string => {
    if (b < 1024) return `${b} B`;
    const units = ['KB', 'MB', 'GB'];
    let i = -1;
    do {
        b /= 1024;
        i++;
    } while (b >= 1024 && i < units.length - 1);
    return `${b.toFixed(1)} ${units[i]}`;
};
</script>

<template>
    <div class="space-y-5">
        <!-- Current file (edit mode only) -->
        <div v-if="mode === 'edit' && document" class="space-y-2">
            <Label class="text-sm font-medium">{{ __('Current File') }}</Label>
            <div class="flex items-center gap-3 rounded-lg border bg-muted/30 p-3">
                <FileText class="h-6 w-6 text-primary shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ document.original_filename }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ document.human_size }} · {{ (document.extension || '').toUpperCase() }}
                    </p>
                </div>
            </div>
        </div>

        <!-- File picker -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">
                {{ mode === 'edit' ? __('Replace File') : __('File') }}
                <span v-if="mode === 'create'" class="text-destructive">*</span>
                <span v-else class="text-xs text-muted-foreground">{{ __('(optional)') }}</span>
            </Label>

            <div v-if="!form.file" class="rounded-lg border-2 border-dashed border-muted p-6 text-center">
                <Upload class="mx-auto h-8 w-8 text-muted-foreground" />
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ __('PDF, Word, Excel, PowerPoint, JPG, PNG · max 40 MB') }}
                </p>
                <Button type="button" variant="outline" class="mt-3" @click="fileInput?.click()">
                    {{ mode === 'edit' ? __('Choose new file') : __('Choose file') }}
                </Button>
                <input ref="fileInput" type="file" class="hidden" :accept="acceptedMimes" @change="handleFileChange" />
            </div>

            <div v-else class="flex items-center gap-3 rounded-lg border p-3"
                :class="mode === 'edit' ? 'bg-primary/10' : 'bg-muted/30'">
                <FileText class="h-6 w-6 text-primary shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ form.file.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ humanSize(form.file.size) }}
                        <span v-if="mode === 'edit'"> · {{ __('will replace current file') }}</span>
                    </p>
                </div>
                <Button type="button" variant="ghost" size="icon" @click="removeFile">
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <p v-if="form.errors.file" class="text-xs text-destructive">{{ form.errors.file }}</p>
        </div>

        <!-- Name -->
        <div class="space-y-2">
            <Label for="document-name" class="text-sm font-medium">
                {{ __('Display Name') }} <span class="text-destructive">*</span>
            </Label>
            <Input id="document-name" v-model="form.name" :placeholder="__('e.g. Employee Handbook 2026')" />
            <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
        </div>

        <!-- Description -->
        <div class="space-y-2">
            <Label for="document-description" class="text-sm font-medium">{{ __('Description') }}</Label>
            <TiptapEditor id="document-description" v-model="form.description" rows="3"
                :placeholder="__('Optional notes about this document')" />
            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
        </div>

        <!-- Upload progress -->
        <div v-if="form.processing && form.progress" class="space-y-1">
            <div class="h-2 w-full rounded bg-muted overflow-hidden">
                <div class="h-full bg-primary transition-all" :style="{ width: `${form.progress.percentage || 0}%` }" />
            </div>
            <p class="text-xs text-muted-foreground">{{ form.progress.percentage || 0 }}%</p>
        </div>
    </div>
</template>

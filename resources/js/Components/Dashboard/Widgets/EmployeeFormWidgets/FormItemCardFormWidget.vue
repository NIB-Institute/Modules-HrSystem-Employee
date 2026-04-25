<script setup lang="ts">
import { useTranslation } from '@/composables/useTranslation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Trash2 } from 'lucide-vue-next';

interface Props {
    title: string;
    index?: number;
}

defineProps<Props>();

const emit = defineEmits<{
    remove: [];
}>();

const { __ } = useTranslation();
</script>

<template>
    <div class="rounded-lg border bg-muted/30 p-4 transition-colors hover:border-muted-foreground/30">
        <div class="mb-4 flex items-center justify-between border-b pb-3">
            <div class="flex items-center gap-2">
                <Badge variant="secondary" class="font-medium">
                    {{ title }}{{ index !== undefined ? ` #${index + 1}` : '' }}
                </Badge>
            </div>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="h-8 gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                @click="emit('remove')"
            >
                <Trash2 class="h-3.5 w-3.5" />
                <span class="text-xs">{{ __('Remove') }}</span>
            </Button>
        </div>
        <slot />
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Plus, UserCircle } from 'lucide-vue-next';

interface Props {
    title: string;
    addLabel: string;
    showAddButton?: boolean;
    emptyMessage: string;
    isEmpty: boolean;
    hasBorderTop?: boolean;
}

withDefaults(defineProps<Props>(), {
    showAddButton: true,
    hasBorderTop: false,
});

const emit = defineEmits<{
    add: [];
}>();
</script>

<template>
    <div class="space-y-4" :class="{ 'border-t border-dashed pt-6': hasBorderTop }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <UserCircle class="h-4 w-4 text-muted-foreground" />
                <h4 class="text-sm font-medium">{{ title }}</h4>
            </div>
            <Button
                v-if="showAddButton"
                type="button"
                variant="outline"
                size="sm"
                class="gap-1.5"
                @click="emit('add')"
            >
                <Plus class="h-3.5 w-3.5" />
                <span>{{ addLabel }}</span>
            </Button>
        </div>
        <slot />
        <div v-if="isEmpty" class="flex items-center justify-center rounded-lg border border-dashed bg-muted/20 p-6">
            <p class="text-sm text-muted-foreground">{{ emptyMessage }}</p>
        </div>
    </div>
</template>

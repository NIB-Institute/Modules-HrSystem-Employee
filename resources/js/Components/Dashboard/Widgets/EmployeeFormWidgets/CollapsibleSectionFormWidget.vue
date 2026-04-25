<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDown, Plus } from 'lucide-vue-next';
import type { Component } from 'vue';

interface Props {
    title: string;
    description?: string;
    icon?: Component;
    showAdd?: boolean;
    addLabel?: string;
    defaultOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    showAdd: false,
    addLabel: 'Add',
    defaultOpen: true,
});

const emit = defineEmits<{
    add: [];
}>();

const isOpen = ref(props.defaultOpen);

const handleAdd = () => {
    isOpen.value = true;
    emit('add');
};

defineExpose({ isOpen });
</script>

<template>
    <Collapsible v-model:open="isOpen">
        <Card>
            <CardHeader class="pb-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <component :is="icon" v-if="icon" class="h-4 w-4 text-primary" />
                            {{ title }}
                        </CardTitle>
                        <CardDescription v-if="description">{{ description }}</CardDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="showAdd"
                            type="button"
                            variant="outline"
                            size="sm"
                            class="gap-1.5"
                            @click="handleAdd"
                        >
                            <Plus class="h-3.5 w-3.5" />
                            <span>{{ addLabel }}</span>
                        </Button>
                        <CollapsibleTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                <ChevronDown
                                    class="h-4 w-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': isOpen }"
                                />
                            </Button>
                        </CollapsibleTrigger>
                    </div>
                </div>
            </CardHeader>
            <CollapsibleContent>
                <CardContent class="pt-0">
                    <slot />
                </CardContent>
            </CollapsibleContent>
        </Card>
    </Collapsible>
</template>

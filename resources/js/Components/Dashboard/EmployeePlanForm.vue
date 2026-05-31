<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/composables/useTranslation';
import TiptapEditor from '@/components/TiptapEditor.vue';

export interface EmployeePlanFormShape {
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    start_time: string;
    end_time: string;
    priority: string;
    location: string;
    telegram_group_chat_id: string;
    telegram_group_name: string;
    schedule_mode: string;
    is_recurring: boolean;
    recurrence_type: string;
    status: string;
    valid_for_months: number | null;
    errors: Record<string, string>;
}

interface Props {
    form: EmployeePlanFormShape;
    mode: 'create' | 'edit';
    priorities: string[];
    scheduleModes: string[];
    recurrenceTypes: string[];
}

const props = defineProps<Props>();

const { __ } = useTranslation();
</script>

<template>
    <div class="space-y-4">
        <!-- Title -->
        <div class="space-y-2">
            <Label for="title">
                {{ __('Title') }} <span class="text-destructive">*</span>
            </Label>
            <Input
                id="title"
                v-model="props.form.title"
                :placeholder="__('Plan title')"
                :class="{ 'border-destructive': props.form.errors.title }"
            />
            <p v-if="props.form.errors.title" class="text-xs text-destructive">
                {{ props.form.errors.title }}
            </p>
        </div>

        <!-- Date range -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_date">
                    {{ __('Start Date') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="start_date"
                    type="date"
                    v-model="props.form.start_date"
                    :class="{ 'border-destructive': props.form.errors.start_date }"
                />
                <p v-if="props.form.errors.start_date" class="text-xs text-destructive">
                    {{ props.form.errors.start_date }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="end_date">
                    {{ __('End Date') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="end_date"
                    type="date"
                    v-model="props.form.end_date"
                    :class="{ 'border-destructive': props.form.errors.end_date }"
                />
                <p v-if="props.form.errors.end_date" class="text-xs text-destructive">
                    {{ props.form.errors.end_date }}
                </p>
            </div>
        </div>

        <!-- Time range -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="start_time">{{ __('Start Time') }}</Label>
                <Input id="start_time" type="time" v-model="props.form.start_time" />
                <p v-if="props.form.errors.start_time" class="text-xs text-destructive">
                    {{ props.form.errors.start_time }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="end_time">{{ __('End Time') }}</Label>
                <Input id="end_time" type="time" v-model="props.form.end_time" />
                <p v-if="props.form.errors.end_time" class="text-xs text-destructive">
                    {{ props.form.errors.end_time }}
                </p>
            </div>
        </div>

        <!-- Priority + Schedule mode -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="priority">
                    {{ __('Priority') }} <span class="text-destructive">*</span>
                </Label>
                <Select v-model="props.form.priority">
                    <SelectTrigger :class="{ 'border-destructive': props.form.errors.priority }">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="p in props.priorities" :key="p" :value="p">
                            {{ __(p) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="props.form.errors.priority" class="text-xs text-destructive">
                    {{ props.form.errors.priority }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="schedule_mode">
                    {{ __('Schedule Mode') }} <span class="text-destructive">*</span>
                </Label>
                <Select v-model="props.form.schedule_mode">
                    <SelectTrigger :class="{ 'border-destructive': props.form.errors.schedule_mode }">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="m in props.scheduleModes" :key="m" :value="m">
                            {{ __(m) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="props.form.errors.schedule_mode" class="text-xs text-destructive">
                    {{ props.form.errors.schedule_mode }}
                </p>
            </div>
        </div>

        <!-- Location + Valid for -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="location">{{ __('Location') }}</Label>
                <Input id="location" v-model="props.form.location" :placeholder="__('Optional location')" />
                <p v-if="props.form.errors.location" class="text-xs text-destructive">
                    {{ props.form.errors.location }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="valid_for_months">
                    {{ __('Valid for (months)') }}
                </Label>
                <Input
                    id="valid_for_months"
                    type="number"
                    min="1"
                    max="120"
                    v-model.number="props.form.valid_for_months"
                    :placeholder="__('e.g. 24 — leave empty for no expiry')"
                />
                <p class="text-xs text-muted-foreground">
                    {{ __('Each assignee\'s expiry = completed_at + this many months.') }}
                </p>
                <p v-if="props.form.errors.valid_for_months" class="text-xs text-destructive">
                    {{ props.form.errors.valid_for_months }}
                </p>
            </div>
        </div>

        <!-- Telegram Group (for plan reminders) -->
        <div class="rounded-lg border bg-muted/30 p-4 space-y-3">
            <div class="space-y-1">
                <Label class="text-sm font-medium">{{ __('Telegram Group (Reminders)') }}</Label>
                <p class="text-xs text-muted-foreground">
                    {{ __('When set, assignment and countdown reminders for this plan are posted to this Telegram chat. Leave empty to disable.') }}
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="telegram_group_chat_id" class="text-xs font-medium">{{ __('Group Chat ID') }}</Label>
                    <Input
                        id="telegram_group_chat_id"
                        v-model="props.form.telegram_group_chat_id"
                        :placeholder="__('e.g. -1001234567890')"
                    />
                    <p v-if="props.form.errors.telegram_group_chat_id" class="text-xs text-destructive">
                        {{ props.form.errors.telegram_group_chat_id }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="telegram_group_name" class="text-xs font-medium">{{ __('Group Name (label)') }}</Label>
                    <Input
                        id="telegram_group_name"
                        v-model="props.form.telegram_group_name"
                        :placeholder="__('e.g. Morning Shift Team')"
                    />
                    <p v-if="props.form.errors.telegram_group_name" class="text-xs text-destructive">
                        {{ props.form.errors.telegram_group_name }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Recurring -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-2 pt-6">
                <input id="is_recurring" type="checkbox" v-model="props.form.is_recurring" class="h-4 w-4" />
                <Label for="is_recurring">{{ __('Recurring') }}</Label>
            </div>
            <div v-if="props.form.is_recurring" class="space-y-2">
                <Label for="recurrence_type">
                    {{ __('Recurrence') }} <span class="text-destructive">*</span>
                </Label>
                <Select v-model="props.form.recurrence_type">
                    <SelectTrigger :class="{ 'border-destructive': props.form.errors.recurrence_type }">
                        <SelectValue :placeholder="__('Select recurrence')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="r in props.recurrenceTypes" :key="r" :value="r">
                            {{ __(r) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="props.form.errors.recurrence_type" class="text-xs text-destructive">
                    {{ props.form.errors.recurrence_type }}
                </p>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
            <Label for="description">{{ __('Description') }}</Label>
            <TiptapEditor
                id="description"
                v-model="props.form.description"
                :placeholder="__('Optional details about this plan...')"
                rows="3"
            />
            <p v-if="props.form.errors.description" class="text-xs text-destructive">
                {{ props.form.errors.description }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTranslation } from '@/composables/useTranslation';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Trash2, User, Calendar, Briefcase, Phone, Mail, AlertCircle, Heart } from 'lucide-vue-next';
import type { FamilyMemberFormData, Gender } from '../../../../types';

const { __ } = useTranslation();

interface Props {
    member: FamilyMemberFormData;
    title: string;
    index?: number;
    showGender?: boolean;
    showEmail?: boolean;
    showDependent?: boolean;
    genderOptions?: { value: string; label: string }[];
}

const props = withDefaults(defineProps<Props>(), {
    showGender: false,
    showEmail: false,
    showDependent: false,
    genderOptions: () => [
        { value: 'male', label: 'Male' },
        { value: 'female', label: 'Female' },
        { value: 'other', label: 'Other' },
    ],
});

const emit = defineEmits<{
    remove: [];
}>();

const memberGender = computed({
    get: () => props.member.gender || undefined,
    set: (value: string | undefined) => {
        props.member.gender = (value as Gender) || null;
    },
});
</script>

<template>
    <div class="rounded-lg border bg-muted/30 p-4 transition-colors hover:border-muted-foreground/30">
        <div class="mb-4 flex items-center justify-between border-b pb-3">
            <Badge variant="secondary" class="font-medium">
                {{ title }}{{ index !== undefined ? ` #${index + 1}` : '' }}
            </Badge>
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
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Name') }} <span class="text-destructive">*</span></Label>
                <div class="relative">
                    <User class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input v-model="member.name" :placeholder="__('Full name')" class="pl-10 bg-background" />
                </div>
            </div>
            <div v-if="showGender" class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Gender') }}</Label>
                <Select v-model="memberGender">
                    <SelectTrigger class="bg-background">
                        <SelectValue :placeholder="__('Select gender')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="opt in genderOptions" :key="opt.value" :value="opt.value">
                            {{ __(opt.label) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Date of Birth') }}</Label>
                <div class="relative">
                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input v-model="member.date_of_birth" type="date" class="pl-10 bg-background" />
                </div>
            </div>
            <div class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Occupation') }}</Label>
                <div class="relative">
                    <Briefcase class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input v-model="member.occupation" :placeholder="__('Job / Occupation')" class="pl-10 bg-background" />
                </div>
            </div>
            <div class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Phone Number') }}</Label>
                <div class="relative">
                    <Phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input v-model="member.phone_number" placeholder="+1 234 567 8900" class="pl-10 bg-background" />
                </div>
            </div>
            <div v-if="showEmail" class="space-y-2">
                <Label class="text-xs font-medium">{{ __('Email') }}</Label>
                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input v-model="member.email" type="email" placeholder="email@example.com" class="pl-10 bg-background" />
                </div>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 rounded-lg border bg-background/50 p-3">
            <div class="flex items-center gap-2">
                <Switch v-model="member.is_emergency_contact" />
                <div class="flex items-center gap-1.5">
                    <AlertCircle class="h-3.5 w-3.5 text-muted-foreground" />
                    <Label class="text-sm font-normal">{{ __('Emergency Contact') }}</Label>
                </div>
            </div>
            <div v-if="showDependent" class="flex items-center gap-2">
                <Switch v-model="member.is_dependent" />
                <div class="flex items-center gap-1.5">
                    <Heart class="h-3.5 w-3.5 text-muted-foreground" />
                    <Label class="text-sm font-normal">{{ __('Dependent') }}</Label>
                </div>
            </div>
        </div>
    </div>
</template>

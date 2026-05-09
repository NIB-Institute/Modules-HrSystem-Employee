<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, CalendarRange, Clock, User } from 'lucide-vue-next';
import { useTranslation } from '@/composables/useTranslation';

interface AvailabilityResource {
    uuid: string;
    day_of_week: string;
    day_label: string;
    start_time: string | null;
    end_time: string | null;
    is_active: boolean;
    employee?: {
        full_name: string;
        employee_code: string;
    } | null;
}

interface Props {
    availability: AvailabilityResource;
}

const props = defineProps<Props>();

const { __ } = useTranslation();
const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const confirmed = ref(false);

const form = useForm({});

const handleSubmit = () => {
    form.delete(`/dashboard/employee-availabilities/${props.availability.uuid}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(__('Availability slot deleted successfully.'));
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="__('Delete Availability Slot')"
        :description="__('This action cannot be undone')"
        mode="delete"
        size="md"
        :submit-text="__('Delete Slot')"
        :loading="form.processing"
        :disabled="!confirmed"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <div class="space-y-3 rounded-lg border bg-muted/30 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                        <CalendarRange class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <p class="font-medium">{{ props.availability.day_label }}</p>
                        <Badge :variant="props.availability.is_active ? 'default' : 'secondary'">
                            {{ props.availability.is_active ? __('Active') : __('Inactive') }}
                        </Badge>
                    </div>
                </div>

                <div v-if="props.availability.employee" class="flex items-center gap-2 text-sm text-muted-foreground">
                    <User class="h-4 w-4" />
                    <span>{{ props.availability.employee.full_name }} ({{ props.availability.employee.employee_code }})</span>
                </div>

                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <Clock class="h-4 w-4" />
                    <span>{{ props.availability.start_time }} – {{ props.availability.end_time }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        {{ __('You are about to delete this availability slot') }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ __('This slot will be soft-deleted and can be restored from trash.') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox id="confirmed" v-model="confirmed" />
                <div class="space-y-1">
                    <Label for="confirmed" class="cursor-pointer font-medium">
                        {{ __('I confirm this deletion') }}
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        {{ __('I understand this action removes the availability slot.') }}
                    </p>
                </div>
            </div>
        </div>
    </ModalForm>
</template>

<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Clock } from 'lucide-vue-next';
import type { Attendance } from '@employee/types';

interface BulkDeleteProps {
    attendances: Attendance[];
}

const props = defineProps<BulkDeleteProps>();

const { show, close, redirect } = useModal();
const confirmed = ref(false);

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

watch(confirmed, () => {
    form.clearErrors();
});

const canSubmit = computed(() => confirmed.value === true);

const form = useForm({
    uuids: props.attendances.map((a) => a.uuid),
});

const handleSubmit = () => {
    form.delete('/dashboard/attendances/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            close();
            redirect();
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
        :title="`Delete ${attendances.length} Attendance Record${attendances.length > 1 ? 's' : ''}`"
        description="This action will move the selected attendance records to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${attendances.length} Record${attendances.length > 1 ? 's' : ''}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <!-- Warning -->
        <div class="rounded-lg border border-destructive/20 bg-destructive/5 p-4">
            <div class="flex items-start gap-3">
                <AlertTriangle class="h-5 w-5 text-destructive mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-destructive">Warning</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        You are about to delete {{ attendances.length }} attendance record{{ attendances.length > 1 ? 's' : '' }}.
                        This will move them to trash.
                    </p>
                </div>
            </div>
        </div>

        <!-- Items to delete -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">Records to delete:</Label>
            <div class="max-h-[200px] overflow-y-auto rounded-md border p-3 space-y-2">
                <div
                    v-for="attendance in attendances"
                    :key="attendance.uuid"
                    class="flex items-center gap-3 p-2 rounded-md bg-muted/50"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-primary/10">
                        <Clock class="h-4 w-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <span class="font-medium">{{ attendance.employee_name }}</span>
                        <span class="text-xs text-muted-foreground ml-2">
                            {{ attendance.attendance_date_formatted }}
                        </span>
                    </div>
                    <Badge :variant="attendance.status === 'present' ? 'default' : 'secondary'">
                        {{ attendance.status_label }}
                    </Badge>
                </div>
            </div>
        </div>

        <!-- Confirmation Checkbox -->
        <div class="flex items-center gap-2">
            <Checkbox
                id="confirm-delete"
                :model-value="confirmed"
                @update:model-value="confirmed = $event as boolean"
            />
            <Label for="confirm-delete" class="text-sm cursor-pointer">
                I understand that this will delete {{ attendances.length }} attendance record{{ attendances.length > 1 ? 's' : '' }}
            </Label>
        </div>
    </ModalForm>
</template>

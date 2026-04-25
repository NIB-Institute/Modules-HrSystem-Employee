<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { AlertTriangle } from 'lucide-vue-next';
import type { Employee } from '@employee/types';

interface BulkDeleteProps {
    employees: Employee[];
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
    uuids: props.employees.map((e) => e.uuid),
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const handleSubmit = () => {
    form.delete('/dashboard/employees/bulk-delete', {
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
        :title="`Delete ${employees.length} Employee${employees.length > 1 ? 's' : ''}`"
        description="This action will move the selected employees to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${employees.length} Employee${employees.length > 1 ? 's' : ''}`"
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
                        You are about to delete {{ employees.length }} employee{{ employees.length > 1 ? 's' : '' }}.
                        This will move them to trash.
                    </p>
                </div>
            </div>
        </div>

        <!-- Items to delete -->
        <div class="space-y-2">
            <Label class="text-sm font-medium">Employees to delete:</Label>
            <div class="max-h-[200px] overflow-y-auto rounded-md border p-3 space-y-2">
                <div
                    v-for="employee in employees"
                    :key="employee.uuid"
                    class="flex items-center gap-3 p-2 rounded-md bg-muted/50"
                >
                    <Avatar class="h-8 w-8">
                        <AvatarImage :src="employee.avatar_url || ''" :alt="employee.full_name" />
                        <AvatarFallback>{{ getInitials(employee.full_name) }}</AvatarFallback>
                    </Avatar>
                    <div>
                        <span class="font-medium">{{ employee.full_name }}</span>
                        <span class="text-xs text-muted-foreground ml-2">
                            ({{ employee.employee_code }})
                        </span>
                    </div>
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
                I understand that this will delete {{ employees.length }} employee{{ employees.length > 1 ? 's' : '' }}
            </Label>
        </div>
    </ModalForm>
</template>

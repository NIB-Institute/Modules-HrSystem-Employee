<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    CheckCircle,
    UserCircle,
    Key,
    Shield,
    UserX,
    UserPlus,
} from 'lucide-vue-next';
import type { EmployeeUser } from '../../types';

interface Props {
    employeeUuid: string;
    employeeName: string;
    employeeEmail: string | null;
    employeePhone: string | null;
    hasAccount: boolean;
    user: EmployeeUser | null;
}

const props = defineProps<Props>();

// Check if employee can create account (has email or phone)
const canCreateAccount = computed(() => !!props.employeeEmail || !!props.employeePhone);

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const openCreateAccountModal = () => {
    router.visit(`/dashboard/employees/${props.employeeUuid}/create-account`);
};
</script>

<template>
    <!-- Account Status Card -->
    <Card>
        <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
                <CardTitle class="text-base flex items-center gap-2">
                    <UserCircle class="h-5 w-5" />
                    Account Status
                </CardTitle>
                <Badge v-if="hasAccount" variant="default" class="bg-green-500">
                    <CheckCircle class="h-3 w-3 mr-1" /> Has Account
                </Badge>
                <Badge v-else variant="secondary">
                    <UserX class="h-3 w-3 mr-1" /> No Account
                </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <!-- Has Account -->
            <div v-if="hasAccount && user" class="space-y-4">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-muted-foreground">Login Identifier</p>
                        <p class="text-sm font-medium">{{ user.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Account Name</p>
                        <p class="text-sm font-medium">{{ user.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Account Created</p>
                        <p class="text-sm font-medium">{{ formatDate(user.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Roles</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <Badge
                                v-for="role in user.roles"
                                :key="role"
                                variant="outline"
                                class="text-xs"
                            >
                                <Shield class="h-3 w-3 mr-1" />
                                {{ role }}
                            </Badge>
                            <span v-if="user.roles.length === 0" class="text-sm text-muted-foreground">No roles</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/dashboard/employees/${employeeUuid}/change-password`">
                            <Key class="h-4 w-4 mr-2" /> Change Password
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- No Account -->
            <div v-else class="text-center py-6">
                <UserX class="h-12 w-12 mx-auto text-muted-foreground mb-3" />
                <p class="text-muted-foreground mb-4">This employee does not have a login account.</p>
                <p class="text-sm text-muted-foreground mb-4">Create an account to allow the employee to access self-service features.</p>
                <Button v-if="canCreateAccount" @click="openCreateAccountModal">
                    <UserPlus class="h-4 w-4 mr-2" /> Create Account
                </Button>
                <p v-else class="text-sm text-destructive">Employee must have an email or phone number to create an account.</p>
            </div>
        </CardContent>
    </Card>
</template>

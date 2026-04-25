import { z } from 'zod';

export const permissionRequestSchema = z.object({
    employee_id: z.number().min(1, 'Employee is required'),
    type: z.enum(['leave', 'overtime', 'remote', 'early_leave', 'late_arrival', 'other'], {
        errorMap: () => ({ message: 'Please select a request type' }),
    }),
    reason: z.string().min(10, 'Reason must be at least 10 characters').max(1000, 'Reason must not exceed 1000 characters'),
    from_date: z.string().min(1, 'From date is required'),
    to_date: z.string().min(1, 'To date is required'),
});

export const reviewPermissionRequestSchema = z.object({
    action: z.enum(['approve', 'reject'], {
        errorMap: () => ({ message: 'Please select an action' }),
    }),
    review_note: z.string().max(500, 'Review note must not exceed 500 characters').nullable().optional(),
    rejected_status: z.boolean().optional(),
    rejected_reason: z.string().max(500, 'Rejected reason must not exceed 500 characters').nullable().optional(),
});

export type PermissionRequestFormSchema = z.infer<typeof permissionRequestSchema>;
export type ReviewPermissionRequestFormSchema = z.infer<typeof reviewPermissionRequestSchema>;

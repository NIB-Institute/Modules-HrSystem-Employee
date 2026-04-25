import { z } from 'zod';

export const employeeTypeSchema = z.object({
    name: z.string().min(1, 'Name is required').max(100, 'Name must not exceed 100 characters'),
    description: z.string().max(500, 'Description must not exceed 500 characters').nullable().optional(),
    time_start: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/, 'Invalid time format').nullable().optional(),
    time_end: z.string().regex(/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/, 'Invalid time format').nullable().optional(),
    status: z.boolean(),
});

export type EmployeeTypeFormSchema = z.infer<typeof employeeTypeSchema>;

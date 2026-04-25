import { z } from 'zod';
import { toTypedSchema } from '@vee-validate/zod';

/**
 * Helper for optional number fields that may come as strings from backend/inputs.
 * Handles: "75000.00" -> 75000, "" -> null, null -> null, undefined -> undefined
 */
const coerceOptionalNumber = (schema: z.ZodNumber = z.number()) =>
    z.preprocess((val) => {
        if (val === '' || val === null || val === undefined) return null;
        const num = Number(val);
        return isNaN(num) ? null : num;
    }, schema.nullable().optional());

// Zod schema for academic level validation
export const academicLevelSchema = z.object({
    _key: z.number(),
    id: z.number().optional(),
    level: z.enum(['high_school', 'vocational', 'associate', 'bachelor', 'master', 'doctorate', 'other']).nullable(),
    institution: z
        .string({ required_error: 'Institution is required' })
        .min(1, 'Institution is required')
        .max(255, 'Institution must be less than 255 characters'),
    field_of_study: z.string().max(255).optional().nullable().or(z.literal('')),
    degree: z.string().max(255).optional().nullable().or(z.literal('')),
    start_date: z.string().optional().nullable().or(z.literal('')),
    end_date: z.string().optional().nullable().or(z.literal('')),
    gpa: coerceOptionalNumber(z.number().min(0).max(4)),
    certificate: z.string().max(255).optional().nullable().or(z.literal('')),
    notes: z.string().max(500).optional().nullable().or(z.literal('')),
});

// Zod schema for job experience validation
export const jobExperienceSchema = z.object({
    _key: z.number(),
    id: z.number().optional(),
    company: z
        .string({ required_error: 'Company is required' })
        .min(1, 'Company is required')
        .max(255, 'Company must be less than 255 characters'),
    position: z
        .string({ required_error: 'Position is required' })
        .min(1, 'Position is required')
        .max(255, 'Position must be less than 255 characters'),
    employment_type: z.enum(['full_time', 'part_time', 'contract', 'freelance', 'internship']).optional().nullable(),
    province: z.string().max(100).optional().nullable().or(z.literal('')),
    city: z.string().max(100).optional().nullable().or(z.literal('')),
    start_date: z
        .string({ required_error: 'Start date is required' })
        .min(1, 'Start date is required'),
    end_date: z.string().optional().nullable().or(z.literal('')),
    is_current: z.boolean().default(false),
    responsibilities: z.string().max(2000).optional().nullable().or(z.literal('')),
    achievements: z.string().max(2000).optional().nullable().or(z.literal('')),
    reason_for_leaving: z.string().max(500).optional().nullable().or(z.literal('')),
    notes: z.string().max(500).optional().nullable().or(z.literal('')),
});

// Zod schema for foreign language validation
export const foreignLanguageSchema = z.object({
    _key: z.number(),
    id: z.number().optional(),
    language: z
        .string({ required_error: 'Language is required' })
        .min(1, 'Language is required')
        .max(100, 'Language must be less than 100 characters'),
    proficiency: z.enum(['beginner', 'elementary', 'intermediate', 'upper_intermediate', 'advanced', 'native']).nullable(),
    certificate: z.string().max(255).optional().nullable().or(z.literal('')),
    certificate_score: z.string().max(50).optional().nullable().or(z.literal('')),
    notes: z.string().max(500).optional().nullable().or(z.literal('')),
});

// Zod schema for family member validation
export const familyMemberSchema = z.object({
    _key: z.number(),
    id: z.number().optional(),
    relationship: z.enum(['spouse', 'child', 'father', 'mother', 'sibling']),
    name: z
        .string({ required_error: 'Name is required' })
        .min(1, 'Name is required')
        .max(100, 'Name must be less than 100 characters'),
    gender: z.enum(['male', 'female', 'other']).optional().nullable(),
    date_of_birth: z.string().optional().nullable().or(z.literal('')),
    age: coerceOptionalNumber(z.number().int().min(0).max(150)),
    occupation: z.string().max(100).optional().nullable().or(z.literal('')),
    phone_number: z.string().max(20).optional().nullable().or(z.literal('')),
    email: z.string().email('Invalid email').optional().nullable().or(z.literal('')),
    address: z.string().max(500).optional().nullable().or(z.literal('')),
    notes: z.string().max(500).optional().nullable().or(z.literal('')),
    is_emergency_contact: z.boolean().default(false),
    is_dependent: z.boolean().default(false),
});

// Zod schema for Employee form validation
export const employeeSchema = z.object({
    employee_code: z
        .string({ required_error: 'Employee code is required' })
        .min(1, 'Employee code is required')
        .max(50, 'Employee code must be less than 50 characters'),
    first_name: z
        .string({ required_error: 'First name is required' })
        .min(1, 'First name is required')
        .max(100, 'First name must be less than 100 characters'),
    last_name: z
        .string({ required_error: 'Last name is required' })
        .min(1, 'Last name is required')
        .max(100, 'Last name must be less than 100 characters'),
    email: z
        .string()
        .email('Invalid email address')
        .optional()
        .nullable()
        .or(z.literal('')),
    phone_number: z
        .string()
        .max(20, 'Phone number must be less than 20 characters')
        .optional()
        .nullable(),
    gender: z.enum(['male', 'female', 'other']).optional().nullable(),
    marital_status: z.enum(['single', 'married']).optional().nullable(),
    date_of_birth: z.string().optional().nullable(),
    birth_place: z
        .string()
        .max(255, 'Birth place must be less than 255 characters')
        .optional()
        .nullable(),
    ethnicity: z
        .string()
        .max(100, 'Ethnicity must be less than 100 characters')
        .optional()
        .nullable()
        .or(z.literal('')),
    current_address: z
        .string()
        .max(500, 'Address must be less than 500 characters')
        .optional()
        .nullable(),
    school_id: z.number().int().positive().optional().nullable(),
    department_id: z.number().int().positive().optional().nullable(),
    position_id: z.number().int().positive().optional().nullable(),
    job_title: z
        .string()
        .max(100, 'Job title must be less than 100 characters')
        .optional()
        .nullable(),
    employee_type: z.enum(['full_time', 'part_time', 'contract', 'intern']).optional().nullable(),
    salary: coerceOptionalNumber(z.number().min(0, 'Salary must be positive')),
    hire_date: z.string().optional().nullable(),
    probation_date: z.string().optional().nullable(),
    probation_end_date: z.string().optional().nullable(),
    certificate: z
        .string()
        .max(255, 'Certificate must be less than 255 characters')
        .optional()
        .nullable(),
    certificate_image: z.string().optional().nullable(),
    certificate_code: z
        .string()
        .max(100, 'Certificate code must be less than 100 characters')
        .optional()
        .nullable(),
    avatar_url: z.string().optional().nullable(),
    status: z.boolean().default(true),
    // Family members array
    family_members: z.array(familyMemberSchema).optional().default([]),
    // Academic levels array
    academic_levels: z.array(academicLevelSchema).optional().default([]),
    // Foreign languages array
    foreign_languages: z.array(foreignLanguageSchema).optional().default([]),
    // Job experiences array
    job_experiences: z.array(jobExperienceSchema).optional().default([]),
});

// TypedSchema for vee-validate
export const employeeValidationSchema = toTypedSchema(employeeSchema);

// Type inference from Zod schema
export type EmployeeFormValues = z.infer<typeof employeeSchema>;

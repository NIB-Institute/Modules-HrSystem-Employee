<?php

namespace Modules\Employee\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Employee\Models\Employee;
use Modules\Employee\Enums\MaritalStatusEnum;
use Modules\Employee\Enums\FamilyRelationshipEnum;
use Modules\Employee\Enums\AcademicLevelEnum;
use Modules\Employee\Enums\LanguageProficiencyEnum;
use Modules\Employee\Enums\EmploymentTypeEnum;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'marital_status' => ['nullable', 'string', Rule::in(MaritalStatusEnum::values())],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'current_address' => ['nullable', 'string', 'max:500'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'department_id' => ['nullable', 'integer', 'exists:school_departments,id'],
            'position_id' => ['nullable', 'integer'],
            'type_employee_id' => ['nullable', 'integer', 'exists:employee_types,id'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'employee_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(Employee::getEmployeeTypes()))],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'hire_date' => ['nullable', 'date'],
            'probation_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after:probation_date'],
            'certificate' => ['nullable', 'string', 'max:255'],
            'certificate_image' => ['nullable', 'string'],
            'certificate_images' => ['nullable', 'array'],
            'certificate_images.*' => ['nullable', 'string'],
            'certificate_code' => ['nullable', 'string', 'max:100'],
            'avatar_url' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],

            // Family members
            'family_members' => ['nullable', 'array'],
            'family_members.*.relationship' => ['required', 'string', Rule::in(FamilyRelationshipEnum::values())],
            'family_members.*.name' => ['required', 'string', 'max:100'],
            'family_members.*.gender' => ['nullable', 'string', 'in:male,female,other'],
            'family_members.*.date_of_birth' => ['nullable', 'date'],
            'family_members.*.age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'family_members.*.occupation' => ['nullable', 'string', 'max:100'],
            'family_members.*.phone_number' => ['nullable', 'string', 'max:20'],
            'family_members.*.email' => ['nullable', 'email', 'max:255'],
            'family_members.*.address' => ['nullable', 'string', 'max:500'],
            'family_members.*.notes' => ['nullable', 'string', 'max:500'],
            'family_members.*.is_emergency_contact' => ['nullable', 'boolean'],
            'family_members.*.is_dependent' => ['nullable', 'boolean'],

            // Academic levels
            'academic_levels' => ['nullable', 'array'],
            'academic_levels.*.level' => ['required', 'string', Rule::in(AcademicLevelEnum::values())],
            'academic_levels.*.institution' => ['required', 'string', 'max:255'],
            'academic_levels.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'academic_levels.*.degree' => ['nullable', 'string', 'max:255'],
            'academic_levels.*.start_date' => ['nullable', 'date'],
            'academic_levels.*.end_date' => ['nullable', 'date', 'after_or_equal:academic_levels.*.start_date'],
            'academic_levels.*.gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'academic_levels.*.certificate' => ['nullable', 'string', 'max:255'],
            'academic_levels.*.notes' => ['nullable', 'string', 'max:500'],

            // Foreign languages
            'foreign_languages' => ['nullable', 'array'],
            'foreign_languages.*.language' => ['required', 'string', 'max:100'],
            'foreign_languages.*.proficiency' => ['required', 'string', Rule::in(LanguageProficiencyEnum::values())],
            'foreign_languages.*.certificate' => ['nullable', 'string', 'max:255'],
            'foreign_languages.*.certificate_score' => ['nullable', 'string', 'max:50'],
            'foreign_languages.*.notes' => ['nullable', 'string', 'max:500'],

            // Job experiences
            'job_experiences' => ['nullable', 'array'],
            'job_experiences.*.company' => ['required', 'string', 'max:255'],
            'job_experiences.*.position' => ['required', 'string', 'max:255'],
            'job_experiences.*.employment_type' => ['nullable', 'string', Rule::in(EmploymentTypeEnum::values())],
            'job_experiences.*.province' => ['nullable', 'string', 'max:100'],
            'job_experiences.*.city' => ['nullable', 'string', 'max:100'],
            'job_experiences.*.start_date' => ['required', 'date'],
            'job_experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:job_experiences.*.start_date'],
            'job_experiences.*.is_current' => ['nullable', 'boolean'],
            'job_experiences.*.responsibilities' => ['nullable', 'string', 'max:2000'],
            'job_experiences.*.achievements' => ['nullable', 'string', 'max:2000'],
            'job_experiences.*.reason_for_leaving' => ['nullable', 'string', 'max:500'],
            'job_experiences.*.notes' => ['nullable', 'string', 'max:500'],

            // Account creation fields
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
        ];

        // If creating account, email is required and must be unique in users table too
        if ($this->boolean('create_account')) {
            $rules['email'] = ['required', 'email', 'max:255', 'unique:employees,email', 'unique:users,email'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'email.required' => 'Email is required when creating an account.',
            'employee_code.unique' => 'This employee code is already in use.',
            'employee_type.in' => 'Invalid employee type selected.',
            'marital_status.in' => 'Invalid marital status selected.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'probation_end_date.after' => 'Probation end date must be after probation start date.',
            'school_id.exists' => 'The selected school does not exist.',
            'department_id.exists' => 'The selected department does not exist.',
            'password.required' => 'Password is required when creating an account.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            // Family member validation messages
            'family_members.*.relationship.required' => 'Family member relationship is required.',
            'family_members.*.relationship.in' => 'Invalid family relationship selected.',
            'family_members.*.name.required' => 'Family member name is required.',
            'family_members.*.name.max' => 'Family member name must be less than 100 characters.',
            'family_members.*.email.email' => 'Please enter a valid email address for family member.',
            // Academic level validation messages
            'academic_levels.*.level.required' => 'Academic level is required.',
            'academic_levels.*.level.in' => 'Invalid academic level selected.',
            'academic_levels.*.institution.required' => 'Institution name is required.',
            'academic_levels.*.institution.max' => 'Institution name must be less than 255 characters.',
            'academic_levels.*.end_date.after_or_equal' => 'End date must be after or equal to start date.',
            // Foreign language validation messages
            'foreign_languages.*.language.required' => 'Language name is required.',
            'foreign_languages.*.language.max' => 'Language name must be less than 100 characters.',
            'foreign_languages.*.proficiency.required' => 'Proficiency level is required.',
            'foreign_languages.*.proficiency.in' => 'Invalid proficiency level selected.',
            // Job experience validation messages
            'job_experiences.*.company.required' => 'Company name is required.',
            'job_experiences.*.company.max' => 'Company name must be less than 255 characters.',
            'job_experiences.*.position.required' => 'Position is required.',
            'job_experiences.*.position.max' => 'Position must be less than 255 characters.',
            'job_experiences.*.employment_type.in' => 'Invalid employment type selected.',
            'job_experiences.*.start_date.required' => 'Start date is required.',
            'job_experiences.*.end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}

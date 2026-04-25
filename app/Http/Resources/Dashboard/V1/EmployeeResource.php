<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\Models\Employee;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status?->value,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'ethnicity' => $this->ethnicity,
            'current_address' => $this->current_address,
            'school_id' => $this->school_id,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'type_employee_id' => $this->type_employee_id,
            'job_title' => $this->job_title,
            'employee_type' => $this->employee_type,
            'employee_type_label' => $this->getEmployeeTypeLabel(),
            'employee_type_name' => $this->whenLoaded('employeeType', fn() => $this->employeeType?->name),
            'salary' => $this->salary,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'probation_date' => $this->probation_date?->format('Y-m-d'),
            'probation_end_date' => $this->probation_end_date?->format('Y-m-d'),
            'certificate' => $this->certificate,
            'certificate_image' => $this->certificate_image,
            'certificate_images' => $this->certificate_images ?? [],
            'certificate_code' => $this->certificate_code,
            'avatar_url' => $this->avatar_url,
            'employee_qr_code' => $this->employee_qr_code,
            'employee_barcode' => $this->employee_barcode,
            'status' => $this->status,
            'is_on_probation' => $this->isOnProbation(),
            'school_name' => $this->whenLoaded('school', fn() => $this->school?->name),
            'department_name' => $this->whenLoaded('department', fn() => $this->department?->name),
            'courses_count' => $this->whenCounted('courses'),
            // Family members
            'family_members' => $this->whenLoaded('familyMembers', fn() => $this->familyMembers->map(fn($member) => [
                'id' => $member->id,
                'relationship' => $member->relationship->value,
                'name' => $member->name,
                'gender' => $member->gender,
                'date_of_birth' => $member->date_of_birth?->format('Y-m-d'),
                'age' => $member->age,
                'occupation' => $member->occupation,
                'phone_number' => $member->phone_number,
                'email' => $member->email,
                'address' => $member->address,
                'notes' => $member->notes,
                'is_emergency_contact' => $member->is_emergency_contact,
                'is_dependent' => $member->is_dependent,
            ])),
            // Academic levels
            'academic_levels' => $this->whenLoaded('academicLevels', fn() => $this->academicLevels->map(fn($level) => [
                'id' => $level->id,
                'level' => $level->level->value,
                'institution' => $level->institution,
                'field_of_study' => $level->field_of_study,
                'degree' => $level->degree,
                'start_date' => $level->start_date?->format('Y-m-d'),
                'end_date' => $level->end_date?->format('Y-m-d'),
                'gpa' => $level->gpa,
                'certificate' => $level->certificate,
                'notes' => $level->notes,
            ])),
            // Foreign languages
            'foreign_languages' => $this->whenLoaded('foreignLanguages', fn() => $this->foreignLanguages->map(fn($lang) => [
                'id' => $lang->id,
                'language' => $lang->language,
                'proficiency' => $lang->proficiency->value,
                'certificate' => $lang->certificate,
                'certificate_score' => $lang->certificate_score,
                'notes' => $lang->notes,
            ])),
            // Job experiences
            'job_experiences' => $this->whenLoaded('jobExperiences', fn() => $this->jobExperiences->map(fn($exp) => [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'employment_type' => $exp->employment_type?->value,
                'province' => $exp->province,
                'city' => $exp->city,
                'start_date' => $exp->start_date?->format('Y-m-d'),
                'end_date' => $exp->end_date?->format('Y-m-d'),
                'is_current' => $exp->is_current,
                'responsibilities' => $exp->responsibilities,
                'achievements' => $exp->achievements,
                'reason_for_leaving' => $exp->reason_for_leaving,
                'notes' => $exp->notes,
            ])),
            // User account data
            'user_id' => $this->user_id,
            'has_account' => $this->user_id !== null,
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'email_verified_at' => $this->user->email_verified_at?->toIso8601String(),
                'created_at' => $this->user->created_at?->toIso8601String(),
                'roles' => method_exists($this->user, 'getRoleNames') ? $this->user->getRoleNames() : [],
            ] : null),
            // Attendance counts (when loaded via withCount)
            'attendance_total' => $this->attendance_total ?? null,
            'attendance_present' => $this->attendance_present ?? null,
            'attendance_absent' => $this->attendance_absent ?? null,
            'attendance_late' => $this->attendance_late ?? null,
            'attendance_on_leave' => $this->attendance_on_leave ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function getEmployeeTypeLabel(): string
    {
        $types = Employee::getEmployeeTypes();
        return $types[$this->employee_type] ?? ucfirst($this->employee_type ?? '');
    }
}

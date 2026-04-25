<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Modules\Employee\Enums\FamilyRelationshipEnum;
use Modules\Employee\Enums\MaritalStatusEnum;
use Modules\Employee\Enums\AcademicLevelEnum;
use Modules\Employee\Enums\LanguageProficiencyEnum;
use Modules\Employee\Enums\EmploymentTypeEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeResource;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;
use Modules\School\Models\School;

class GetEmployeeEditDataAction
{
    public function execute(Employee $employee): array
    {
        // Load relationships
        $employee->load(['familyMembers', 'academicLevels', 'foreignLanguages', 'jobExperiences']);
        try {
            $schools = School::where('status', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $schools = collect();
        }

        try {
            $departments = Department::where('status', true)
                ->when($employee->school_id, function ($query) use ($employee) {
                    $query->where('school_id', $employee->school_id);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $departments = collect();
        }

        // Transform employee types to array of {value, label} objects
        $employeeTypes = collect(Employee::getEmployeeTypes())
            ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        return [
            'employee' => (new EmployeeResource($employee))->resolve(),
            'schools' => $schools,
            'departments' => $departments,
            'employeeTypes' => $employeeTypes,
            'maritalStatuses' => MaritalStatusEnum::options(),
            'relationshipTypes' => FamilyRelationshipEnum::options(),
            'academicLevels' => AcademicLevelEnum::options(),
            'languageProficiencies' => LanguageProficiencyEnum::options(),
            'employmentTypes' => EmploymentTypeEnum::options(),
        ];
    }
}

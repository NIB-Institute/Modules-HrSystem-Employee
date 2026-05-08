<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeAvailability;
use Modules\Employee\Models\EmployeePlan;

class GetEmployeePlanAssignmentCreateDataAction
{
    public function execute(?int $planId = null, ?int $employeeId = null): array
    {
        $plans = EmployeePlan::query()
            ->select('id', 'uuid', 'title', 'start_date', 'end_date', 'status')
            ->orderByDesc('start_date')
            ->get();

        $employees = Employee::query()
            ->select('id', 'uuid', 'first_name', 'last_name', 'employee_code')
            ->where('status', true)
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'full_name' => trim("{$e->first_name} {$e->last_name}"),
                'employee_code' => $e->employee_code,
            ]);

        // All availability slots — frontend filters by employee on selection.
        $availabilities = EmployeeAvailability::query()
            ->select('id', 'uuid', 'employee_id', 'day_of_week', 'start_time', 'end_time', 'is_active')
            ->where('is_active', true)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'employee_id' => $a->employee_id,
                'day_of_week' => $a->day_of_week,
                'start_time' => $a->start_time ? substr((string) $a->start_time, 0, 5) : null,
                'end_time' => $a->end_time ? substr((string) $a->end_time, 0, 5) : null,
            ]);

        return [
            'plans' => $plans,
            'employees' => $employees,
            'availabilities' => $availabilities,
            'statuses' => EmployeePlanAssignmentEnum::STATUSES,
            'selectedPlanId' => $planId,
            'selectedEmployeeId' => $employeeId,
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Enums\EmployeePlanEnum;
use Modules\Employee\Models\Employee;

class GetEmployeePlanCreateDataAction
{
    public function execute(?int $employeeId = null): array
    {
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

        return [
            'employees' => $employees,
            'priorities' => EmployeePlanEnum::PRIORITIES,
            'scheduleModes' => EmployeePlanEnum::SCHEDULE_MODES,
            'recurrenceTypes' => EmployeePlanEnum::RECURRENCE_TYPES,
            'statuses' => EmployeePlanEnum::STATUSES,
            'selectedEmployeeId' => $employeeId,
        ];
    }
}

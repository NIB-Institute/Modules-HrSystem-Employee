<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Modules\Employee\Enums\EmployeeAvailabilityEnum;
use Modules\Employee\Models\Employee;

class GetEmployeeAvailabilityCreateDataAction
{
    public function execute(?int $employeeId = null): array
    {
        $employees = Employee::query()
            ->select('id', 'uuid', 'first_name', 'last_name', 'employee_code')
            ->where('status', true)
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id'      => $e->id,
                'uuid'    => $e->uuid,
                'full_name'     => trim("{$e->first_name} {$e->last_name}"),
                'employee_code' => $e->employee_code,
            ]);

        return [
            'employees' => $employees,
            'days'      => EmployeeAvailabilityEnum::DAYS,
            'selectedEmployeeId' => $employeeId,
        ];
    }
}

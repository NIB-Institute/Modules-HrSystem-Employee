<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Modules\Employee\Enums\EmployeeAvailabilityEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeAvailabilityResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeAvailability;

class GetEmployeeAvailabilityEditDataAction
{
    public function execute(EmployeeAvailability $employeeAvailability): array
    {
        $employeeAvailability->load('employee');

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
            'availability' => (new EmployeeAvailabilityResource($employeeAvailability))->resolve(),
            'employees'    => $employees,
            'days'         => EmployeeAvailabilityEnum::DAYS,
        ];
    }
}

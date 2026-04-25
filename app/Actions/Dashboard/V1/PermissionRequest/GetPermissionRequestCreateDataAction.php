<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class GetPermissionRequestCreateDataAction
{
    public function execute(?int $employeeId = null): array
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'uuid', 'employee_code', 'avatar_url')
            ->where('status', true)
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'full_name' => $e->full_name,
                'employee_code' => $e->employee_code,
                'avatar_url' => $e->avatar_url,
            ]);

        return [
            'employees' => $employees,
            'types' => PermissionRequest::getTypes(),
            'typeDescriptions' => PermissionRequest::getTypeDescriptions(),
            'selectedEmployeeId' => $employeeId,
        ];
    }
}

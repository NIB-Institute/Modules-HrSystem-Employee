<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Http\Resources\Dashboard\V1\PermissionRequestResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class GetPermissionRequestEditDataAction
{
    public function execute(PermissionRequest $permissionRequest): array
    {
        $permissionRequest->load(['employee', 'reviewer']);

        $employees = Employee::select('id', 'first_name', 'last_name', 'uuid', 'employee_code')
            ->where('status', true)
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'name' => $e->full_name,
                'code' => $e->employee_code,
            ]);

        return [
            'permissionRequest' => (new PermissionRequestResource($permissionRequest))->resolve(),
            'employees' => $employees,
            'types' => PermissionRequest::getTypes(),
            'typeDescriptions' => PermissionRequest::getTypeDescriptions(),
        ];
    }
}

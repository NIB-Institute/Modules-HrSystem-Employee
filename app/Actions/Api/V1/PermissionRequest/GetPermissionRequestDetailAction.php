<?php

namespace Modules\Employee\Actions\Api\V1\PermissionRequest;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\PermissionRequestResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class GetPermissionRequestDetailAction
{
    /**
     * Get permission request detail.
     */
    public function execute(User $user, string $uuid): array
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        $permissionRequest = PermissionRequest::with(['reviewer'])
            ->where('employee_id', $employee->id)
            ->where('uuid', $uuid)
            ->first();

        if (!$permissionRequest) {
            return [
                'success' => false,
                'message' => 'Permission request not found',
            ];
        }

        return [
            'success' => true,
            'data' => new PermissionRequestResource($permissionRequest),
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Api\V1\PermissionRequest;

use App\Models\User;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class CancelPermissionRequestAction
{
    /**
     * Cancel a pending permission request.
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

        $permissionRequest = PermissionRequest::where('employee_id', $employee->id)
            ->where('uuid', $uuid)
            ->first();

        if (!$permissionRequest) {
            return [
                'success' => false,
                'message' => 'Permission request not found',
            ];
        }

        if (!$permissionRequest->isPending()) {
            return [
                'success' => false,
                'message' => 'Only pending requests can be cancelled',
            ];
        }

        $permissionRequest->delete();

        return [
            'success' => true,
            'message' => 'Permission request cancelled successfully',
        ];
    }
}

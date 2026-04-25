<?php

namespace Modules\Employee\Actions\Api\V1\PermissionRequest;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\PermissionRequestResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class CreatePermissionRequestAction
{
    /**
     * Create a new permission request.
     */
    public function execute(User $user, array $data): array
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        // Check for duplicate pending requests for the same date range and type
        $existingRequest = PermissionRequest::where('employee_id', $employee->id)
            ->where('type', $data['type'])
            ->where('status', PermissionRequest::STATUS_PENDING)
            ->where(function ($query) use ($data) {
                $query->whereBetween('from_date', [$data['from_date'], $data['to_date']])
                    ->orWhereBetween('to_date', [$data['from_date'], $data['to_date']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('from_date', '<=', $data['from_date'])
                          ->where('to_date', '>=', $data['to_date']);
                    });
            })
            ->first();

        if ($existingRequest) {
            return [
                'success' => false,
                'message' => 'You already have a pending request for this date range',
            ];
        }

        $permissionRequest = PermissionRequest::create([
            'employee_id' => $employee->id,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'request_date' => now(),
            'status' => PermissionRequest::STATUS_PENDING,
        ]);

        $permissionRequest->load('reviewer');

        return [
            'success' => true,
            'message' => 'Permission request submitted successfully',
            'data' => new PermissionRequestResource($permissionRequest),
        ];
    }
}

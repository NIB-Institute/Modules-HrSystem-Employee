<?php

namespace Modules\Employee\Actions\Api\V1\Auth;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\EmployeeResource;
use Modules\Employee\Models\Employee;

class GetEmployeeProfileAction
{
    /**
     * Get employee profile for the authenticated user.
     */
    public function execute(User $user): array
    {
        $employee = Employee::with(['user', 'school', 'department', 'employeeType'])
            ->where('user_id', $user->id)
            ->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee profile not found',
            ];
        }

        return [
            'success' => true,
            'employee' => new EmployeeResource($employee),
        ];
    }
}

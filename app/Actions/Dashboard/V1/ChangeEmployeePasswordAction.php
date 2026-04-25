<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Hash;
use Modules\Employee\Models\Employee;

class ChangeEmployeePasswordAction
{
    public function execute(Employee $employee, string $password): array
    {
        if (!$employee->user_id || !$employee->user) {
            return [
                'success' => false,
                'message' => 'This employee does not have a linked user account.',
            ];
        }

        $employee->user->update([
            'password' => Hash::make($password),
        ]);

        return [
            'success' => true,
            'message' => 'Password changed successfully.',
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Api\V1\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Employee\Http\Resources\Api\V1\EmployeeResource;
use Modules\Employee\Models\Employee;

class LoginAction
{
    /**
     * Execute the login action.
     *
     * @throws ValidationException
     */
    public function execute(array $data): array
    {
        $employee = $this->findEmployee($data);

        $this->validateEmployee($employee);
        $this->validateCredentials($employee, $data['password']);
        $this->validateStatus($employee);

        // Create token for the linked user
        $token = $employee->user->createToken('employee-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'employee' => new EmployeeResource($employee),
        ];
    }

    /**
     * Find employee by email or phone.
     */
    private function findEmployee(array $data): ?Employee
    {
        $query = Employee::with(['user', 'school', 'department', 'employeeType']);

        if (!empty($data['email'])) {
            // Try to find by employee email first, then by user email
            $employee = $query->where('email', $data['email'])->first();

            if (!$employee) {
                // Try finding by user email (for phone-based pseudo-email login)
                $employee = Employee::with(['user', 'school', 'department', 'employeeType'])
                    ->whereHas('user', fn ($q) => $q->where('email', $data['email']))
                    ->first();
            }

            return $employee;
        }

        if (!empty($data['phone'])) {
            return $query->where('phone_number', $data['phone'])->first();
        }

        return null;
    }

    /**
     * Validate employee exists and has user account.
     *
     * @throws ValidationException
     */
    private function validateEmployee(?Employee $employee): void
    {
        if (!$employee) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$employee->user_id || !$employee->user) {
            throw ValidationException::withMessages([
                'email' => ['This employee does not have a login account.'],
            ]);
        }
    }

    /**
     * Validate employee credentials against user password.
     *
     * @throws ValidationException
     */
    private function validateCredentials(Employee $employee, string $password): void
    {
        if (!Hash::check($password, $employee->user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }

    /**
     * Validate employee status.
     *
     * @throws ValidationException
     */
    private function validateStatus(Employee $employee): void
    {
        if (!$employee->status) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }
    }
}

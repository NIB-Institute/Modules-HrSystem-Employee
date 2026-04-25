<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Employee\Models\Employee;

class CreateEmployeeAccountAction
{
    public function execute(Employee $employee, string $password, string $loginMethod = 'email'): array
    {
        // Check if employee already has an account
        if ($employee->user_id) {
            return [
                'success' => false,
                'message' => 'This employee already has a linked user account.',
            ];
        }

        // Determine the login identifier based on method
        $loginIdentifier = $this->getLoginIdentifier($employee, $loginMethod);

        if (!$loginIdentifier) {
            $methodLabel = $loginMethod === 'email' ? 'email address' : 'phone number';
            return [
                'success' => false,
                'message' => "Employee must have a {$methodLabel} to create an account with this login method.",
            ];
        }

        // For email login, use email directly
        // For phone login, create a pseudo-email format
        $email = $loginMethod === 'email'
            ? $loginIdentifier
            : $this->createPhoneEmail($loginIdentifier);

        // Check if identifier is already used by another user
        if (User::where('email', $email)->exists()) {
            $methodLabel = $loginMethod === 'email' ? 'email address' : 'phone number';
            return [
                'success' => false,
                'message' => "This {$methodLabel} is already used by another user account.",
            ];
        }

        return DB::transaction(function () use ($employee, $password, $email, $loginMethod) {
            // Create the user account
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Assign employee role if exists
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('employee');
                } catch (\Exception $e) {
                    // Role might not exist, continue without it
                }
            }

            // Link user to employee
            $employee->update(['user_id' => $user->id]);

            $methodLabel = $loginMethod === 'email' ? 'email' : 'phone number';
            return [
                'success' => true,
                'message' => "Account created successfully. Employee can now log in using their {$methodLabel}.",
                'user' => $user,
            ];
        });
    }

    /**
     * Get the login identifier based on method.
     */
    private function getLoginIdentifier(Employee $employee, string $loginMethod): ?string
    {
        if ($loginMethod === 'email') {
            return $employee->email;
        }

        return $employee->phone_number;
    }

    /**
     * Create a pseudo-email for phone-based login.
     * Format: +855123456789 -> 855123456789@phone.local
     */
    private function createPhoneEmail(string $phoneNumber): string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        // Remove the + sign
        $cleaned = ltrim($cleaned, '+');

        return "{$cleaned}@phone.local";
    }
}

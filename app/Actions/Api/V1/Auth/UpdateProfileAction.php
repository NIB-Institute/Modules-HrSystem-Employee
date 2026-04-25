<?php

namespace Modules\Employee\Actions\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Employee\Http\Resources\Api\V1\EmployeeResource;
use Modules\Employee\Models\Employee;

class UpdateProfileAction
{
    /**
     * Update employee profile.
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

        // Debug: Log received data
        \Log::info('UpdateProfile data received:', [
            'has_avatar' => isset($data['avatar']),
            'avatar_type' => isset($data['avatar']) ? get_class($data['avatar']) : 'not set',
            'data_keys' => array_keys($data),
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if (isset($data['avatar']) && $data['avatar']) {
            \Log::info('Avatar file info:', [
                'original_name' => $data['avatar']->getClientOriginalName(),
                'size' => $data['avatar']->getSize(),
                'mime' => $data['avatar']->getMimeType(),
            ]);

            // Delete old avatar if exists (extract path from full URL)
            if ($employee->avatar_url) {
                $oldPath = str_replace(asset('storage/'), '', $employee->avatar_url);
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    \Log::info('Deleted old avatar:', ['path' => $oldPath]);
                }
            }

            // Store new avatar
            $avatarPath = $data['avatar']->store('employees/avatars', 'public');
            \Log::info('Avatar stored at:', ['path' => $avatarPath]);
        }

        // Update employee
        $updateData = [
            'first_name' => $data['first_name'] ?? $employee->first_name,
            'last_name' => $data['last_name'] ?? $employee->last_name,
            'phone_number' => $data['phone_number'] ?? $employee->phone_number,
            'gender' => $data['gender'] ?? $employee->gender,
            'date_of_birth' => $data['date_of_birth'] ?? $employee->date_of_birth,
            'birth_place' => $data['birth_place'] ?? $employee->birth_place,
            'current_address' => $data['current_address'] ?? $employee->current_address,
        ];

        // If avatar was uploaded, save the full URL to avatar_url
        if ($avatarPath) {
            $updateData['avatar_url'] = asset('storage/' . $avatarPath);
        }

        $employee->update($updateData);

        // Refresh the model
        $employee->refresh();
        $employee->load(['school', 'department', 'employeeType', 'user']);

        return [
            'success' => true,
            'message' => 'Profile updated successfully',
            'employee' => new EmployeeResource($employee),
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Api\V1\Auth;

use App\Models\User;

class LogoutAction
{
    /**
     * Logout user (revoke current token).
     */
    public function execute(User $user): array
    {
        // Revoke current token
        $user->currentAccessToken()->delete();

        return [
            'success' => true,
            'message' => 'Logged out successfully',
        ];
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(User $user): array
    {
        // Revoke all tokens
        $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ];
    }
}

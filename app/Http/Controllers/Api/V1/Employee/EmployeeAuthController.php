<?php

namespace Modules\Employee\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Employee\Actions\Api\V1\Auth\GetEmployeeProfileAction;
use Modules\Employee\Actions\Api\V1\Auth\LoginAction;
use Modules\Employee\Actions\Api\V1\Auth\LogoutAction;
use Modules\Employee\Actions\Api\V1\Auth\UpdateProfileAction;
use Modules\Employee\Http\Requests\Api\V1\Employee\LoginRequest;
use Modules\Employee\Http\Requests\Api\V1\Employee\UpdateProfileRequest;

class EmployeeAuthController extends Controller
{
    /**
     * Login employee with email or phone
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json($result);
    }

    /**
     * Logout employee (revoke current token)
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return response()->json($result);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request, LogoutAction $action): JsonResponse
    {
        $result = $action->logoutAll($request->user());

        return response()->json($result);
    }

    /**
     * Get current employee profile
     */
    public function me(Request $request, GetEmployeeProfileAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return response()->json($result);
    }

    /**
     * Update employee profile
     */
    public function updateProfile(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return response()->json($result);
    }
}

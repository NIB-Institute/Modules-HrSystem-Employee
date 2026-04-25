<?php

namespace Modules\Employee\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Employee\Actions\Api\V1\PermissionRequest\CancelPermissionRequestAction;
use Modules\Employee\Actions\Api\V1\PermissionRequest\CreatePermissionRequestAction;
use Modules\Employee\Actions\Api\V1\PermissionRequest\GetPermissionRequestDetailAction;
use Modules\Employee\Actions\Api\V1\PermissionRequest\GetPermissionRequestsAction;
use Modules\Employee\Http\Requests\Api\V1\Employee\StorePermissionRequestRequest;
use Modules\Employee\Models\PermissionRequest;

class PermissionRequestController extends Controller
{
    /**
     * Get list of permission requests for authenticated employee.
     */
    public function index(Request $request, GetPermissionRequestsAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            $request->input('status'),
            $request->input('type'),
            $request->input('per_page', 15)
        );

        return response()->json($result);
    }

    /**
     * Create a new permission request.
     */
    public function store(StorePermissionRequestRequest $request, CreatePermissionRequestAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * Get single permission request detail.
     */
    public function show(Request $request, string $uuid, GetPermissionRequestDetailAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $uuid);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Cancel a pending permission request.
     */
    public function cancel(Request $request, string $uuid, CancelPermissionRequestAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $uuid);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get available permission request types.
     */
    public function types(): JsonResponse
    {
        $types = collect(PermissionRequest::getTypes())->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
                'description' => PermissionRequest::getTypeDescriptions()[$value] ?? '',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}

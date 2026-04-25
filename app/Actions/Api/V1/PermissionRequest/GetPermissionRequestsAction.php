<?php

namespace Modules\Employee\Actions\Api\V1\PermissionRequest;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\PermissionRequestResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class GetPermissionRequestsAction
{
    /**
     * Get permission requests for the authenticated employee.
     */
    public function execute(User $user, ?string $status = null, ?string $type = null, int $perPage = 15): array
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        $query = PermissionRequest::with(['reviewer'])
            ->where('employee_id', $employee->id)
            ->orderBy('request_date', 'desc');

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply type filter
        if ($type) {
            $query->where('type', $type);
        }

        $requests = $query->paginate($perPage);

        // Calculate statistics
        $stats = $this->calculateStats($employee->id);

        return [
            'success' => true,
            'stats' => $stats,
            'data' => PermissionRequestResource::collection($requests)->response()->getData(true),
        ];
    }

    /**
     * Calculate permission request statistics.
     */
    private function calculateStats(int $employeeId): array
    {
        $baseQuery = PermissionRequest::where('employee_id', $employeeId);

        return [
            'total'     => (clone $baseQuery)->count(),
            'pending'   => (clone $baseQuery)->pending()->count(),
            'approved'  => (clone $baseQuery)->approved()->count(),
            'rejected'  => (clone $baseQuery)->rejected()->count(),
        ];
    }
}

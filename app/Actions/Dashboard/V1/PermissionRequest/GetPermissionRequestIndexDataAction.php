<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Http\Resources\Dashboard\V1\PermissionRequestResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\PermissionRequest;

class GetPermissionRequestIndexDataAction
{
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = PermissionRequest::query()->with(['employee', 'reviewer']);

        // Filter by employee
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by type
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        // Filter by status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('from_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('to_date', '<=', $filters['date_to']);
        }

        $requests = $query->latest('request_date')->paginate($perPage);

        $stats = [
            'total' => PermissionRequest::count(),
            'pending' => PermissionRequest::pending()->count(),
            'approved' => PermissionRequest::approved()->count(),
            'rejected' => PermissionRequest::rejected()->count(),
        ];

        // Get employees for filter dropdown
        $employees = Employee::select('id', 'first_name', 'last_name', 'uuid', 'employee_code', 'avatar_url')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'full_name' => $e->full_name,
                'employee_code' => $e->employee_code,
                'avatar_url' => $e->avatar_url,
            ]);

        return [
            'permissionRequests' => [
                'data' => PermissionRequestResource::collection($requests)->resolve(),
                'meta' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
            'employees' => $employees,
            'types' => PermissionRequest::getTypes(),
            'typeDescriptions' => PermissionRequest::getTypeDescriptions(),
            'statuses' => PermissionRequest::getStatuses(),
        ];
    }
}

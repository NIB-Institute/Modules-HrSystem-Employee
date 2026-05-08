<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanAssignmentResource;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class GetEmployeePlanAssignmentIndexDataAction
{
    public function execute(int $perPage = 15, array $filters = []): array
    {
        $query = EmployeePlanAssignment::query()->with(['plan', 'employee', 'availability']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($eq) use ($search) {
                $eq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            })->orWhereHas('plan', function ($pq) use ($search) {
                $pq->where('title', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['employee_plan_id'])) {
            $query->where('employee_plan_id', $filters['employee_plan_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $assignments = $query->latest('assigned_at')->paginate($perPage)->withQueryString();

        $stats = [
            'total' => EmployeePlanAssignment::count(),
            'assigned' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_ASSIGNED)->count(),
            'in_progress' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS)->count(),
            'completed' => EmployeePlanAssignment::where('status', EmployeePlanAssignmentEnum::STATUS_COMPLETED)->count(),
        ];

        $plans = EmployeePlan::query()
            ->select('id', 'uuid', 'title')
            ->orderByDesc('start_date')
            ->limit(200)
            ->get();

        return [
            'assignments' => [
                'data' => EmployeePlanAssignmentResource::collection($assignments)->resolve(),
                'meta' => [
                    'current_page' => $assignments->currentPage(),
                    'last_page' => $assignments->lastPage(),
                    'per_page' => $assignments->perPage(),
                    'total' => $assignments->total(),
                ],
                'links' => $assignments->linkCollection()->toArray(),
            ],
            'filters' => $filters,
            'stats' => $stats,
            'plans' => $plans,
            'statuses' => EmployeePlanAssignmentEnum::STATUSES,
        ];
    }
}

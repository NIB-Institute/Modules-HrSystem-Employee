<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Enums\EmployeePlanEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanResource;
use Modules\Employee\Models\EmployeePlan;

class GetEmployeePlanIndexDataAction
{
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = EmployeePlan::query()->withCount('assignments');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('start_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('end_date', '<=', $filters['date_to']);
        }

        $plans = $query->orderByDesc('start_date')->paginate($perPage)->withQueryString();

        $stats = [
            'total'       => EmployeePlan::count(),
            'scheduled'   => EmployeePlan::where('status', 'scheduled')->count(),
            'in_progress' => EmployeePlan::where('status', 'in_progress')->count(),
            'completed'   => EmployeePlan::where('status', 'completed')->count(),
        ];

        return [
            'plans' => [
                'data' => EmployeePlanResource::collection($plans)->resolve(),
                'meta' => [
                    'current_page'  => $plans->currentPage(),
                    'last_page'     => $plans->lastPage(),
                    'per_page'      => $plans->perPage(),
                    'total'         => $plans->total(),
                ],
                'links' => $plans->linkCollection()->toArray(),
            ],
            'filters'      => $filters,
            'stats'        => $stats,
            'priorities'   => EmployeePlanEnum::PRIORITIES,
            'statuses'     => EmployeePlanEnum::STATUSES,
        ];
    }
}

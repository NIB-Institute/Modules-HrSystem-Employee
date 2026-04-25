<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeTypeResource;
use Modules\Employee\Models\EmployeeType;

class GetEmployeeTypeIndexDataAction
{
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = EmployeeType::query()->withCount('employees');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $employeeTypes = $query->latest()->paginate($perPage);

        $stats = [
            'total' => EmployeeType::count(),
            'active' => EmployeeType::where('status', true)->count(),
            'inactive' => EmployeeType::where('status', false)->count(),
        ];

        return [
            'employeeTypes' => [
                'data' => EmployeeTypeResource::collection($employeeTypes)->resolve(),
                'meta' => [
                    'current_page' => $employeeTypes->currentPage(),
                    'last_page' => $employeeTypes->lastPage(),
                    'per_page' => $employeeTypes->perPage(),
                    'total' => $employeeTypes->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
        ];
    }
}

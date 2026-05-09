<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Enums\EmployeeAvailabilityEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeAvailabilityResource;
use Modules\Employee\Models\EmployeeAvailability;

class GetEmployeeAvailabilityIndexDataAction
{
    public function execute(int $perPage = 15, array $filters = []): array
    {
        $query = EmployeeAvailability::query()->with('employee');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($eq) use ($search) {
                $eq->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['day_of_week']) && $filters['day_of_week'] !== 'all') {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== 'all' && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Sort by employee, then by day-of-week order, then by start_time
        $dayOrderCase = collect(EmployeeAvailabilityEnum::DAYS)
            ->map(fn ($d, $i) => "WHEN '{$d}' THEN {$i}")
            ->implode(' ');

        $availabilities = $query
            ->orderBy('employee_id')
            ->orderByRaw("CASE day_of_week {$dayOrderCase} END")
            ->orderBy('start_time')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total'     => EmployeeAvailability::count(),
            'active'    => EmployeeAvailability::where('is_active', true)->count(),
            'inactive'  => EmployeeAvailability::where('is_active', false)->count(),
            'employees_covered' => EmployeeAvailability::distinct('employee_id')->count('employee_id'),
        ];

        return [
            'availabilities' => [
                'data' => EmployeeAvailabilityResource::collection($availabilities)->resolve(),
                'meta' => [
                    'current_page' => $availabilities->currentPage(),
                    'last_page'    => $availabilities->lastPage(),
                    'per_page'     => $availabilities->perPage(),
                    'total'        => $availabilities->total(),
                ],
                'links'             => $availabilities->linkCollection()->toArray(),
            ],
            'filters' => $filters,
            'stats'   => $stats,
            'days'    => EmployeeAvailabilityEnum::DAYS,
        ];
    }
}

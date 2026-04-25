<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\School\Models\School;

class GetEmployeeIndexDataAction
{
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = Employee::query()->with('employeeType');

        // Date range for attendance (default to current month)
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? now()->format('Y-m-d');

        // Add attendance counts using withCount and date filtering
        $query->withCount([
            'attendances as attendance_total' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('attendance_date', [$dateFrom, $dateTo]);
            },
            'attendances as attendance_present' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('attendance_date', [$dateFrom, $dateTo])
                    ->where('status', Attendance::STATUS_PRESENT);
            },
            'attendances as attendance_absent' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('attendance_date', [$dateFrom, $dateTo])
                    ->where('status', Attendance::STATUS_ABSENT);
            },
            'attendances as attendance_late' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('attendance_date', [$dateFrom, $dateTo])
                    ->where('status', Attendance::STATUS_LATE);
            },
            'attendances as attendance_on_leave' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('attendance_date', [$dateFrom, $dateTo])
                    ->where('status', Attendance::STATUS_ON_LEAVE);
            },
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['employee_type']) && $filters['employee_type'] !== 'all') {
            $query->where('employee_type', $filters['employee_type']);
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $employees = $query->latest()->paginate($perPage);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', true)->count(),
            'inactive' => Employee::where('status', false)->count(),
            'full_time' => Employee::where('employee_type', 'full_time')->count(),
            'part_time' => Employee::where('employee_type', 'part_time')->count(),
            'contract' => Employee::where('employee_type', 'contract')->count(),
        ];

        // Attendance stats for the selected date range
        $attendanceStats = $this->getAttendanceStats($dateFrom, $dateTo);

        // Transform employee types to array of {value, label} objects
        $employeeTypes = collect(Employee::getEmployeeTypes())
            ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();

        // Get schools for filter dropdown (handle case where table doesn't exist)
        try {
            $schools = School::select('id', 'name')
                ->where('status', true)
                ->orderBy('name')
                ->get()
                ->map(fn($school) => ['id' => $school->id, 'name' => $school->name])
                ->all();
        } catch (\Exception $e) {
            $schools = [];
        }

        return [
            'employees' => [
                'data' => EmployeeResource::collection($employees)->resolve(),
                'meta' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                ],
            ],
            'filters' => array_merge($filters, [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]),
            'stats' => $stats,
            'attendanceStats' => $attendanceStats,
            'employeeTypes' => $employeeTypes,
            'schools' => $schools,
        ];
    }

    protected function getAttendanceStats(string $dateFrom, string $dateTo): array
    {
        try {
            $stats = Attendance::query()
                ->whereBetween('attendance_date', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'total_records' => array_sum($stats),
                'present' => $stats[Attendance::STATUS_PRESENT] ?? 0,
                'absent' => $stats[Attendance::STATUS_ABSENT] ?? 0,
                'late' => $stats[Attendance::STATUS_LATE] ?? 0,
                'early_leave' => $stats[Attendance::STATUS_EARLY_LEAVE] ?? 0,
                'half_day' => $stats[Attendance::STATUS_HALF_DAY] ?? 0,
                'on_leave' => $stats[Attendance::STATUS_ON_LEAVE] ?? 0,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];
        } catch (\Exception $e) {
            return [
                'total_records' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'early_leave' => 0,
                'half_day' => 0,
                'on_leave' => 0,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ];
        }
    }
}

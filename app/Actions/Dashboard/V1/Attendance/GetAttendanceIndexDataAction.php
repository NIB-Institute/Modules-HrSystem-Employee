<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Http\Request;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;

class GetAttendanceIndexDataAction
{
    public function execute(Request $request): array
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status');
        $employeeId = $request->input('employee_id');
        $departmentId = $request->input('department_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Attendance::with(['employee', 'department', 'classroom'])
            ->latest('attendance_date');

        // Apply filters
        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($dateFrom) {
            $query->whereDate('attendance_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('attendance_date', '<=', $dateTo);
        }

        $attendances = $query->paginate($perPage);

        // Get stats
        $todayStats = $this->getTodayStats();

        // Get filter options
        $employees = Employee::active()
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->full_name . ' (' . $e->employee_code . ')',
            ]);

        $departments = Department::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'attendances' => $attendances,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'employee_id' => $employeeId,
                'department_id' => $departmentId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'stats' => $todayStats,
            'statuses' => Attendance::getStatuses(),
            'employees' => $employees,
            'departments' => $departments,
        ];
    }

    private function getTodayStats(): array
    {
        $today = today();

        return [
            'total_employees' => Employee::active()->count(),
            'present_today' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_PRESENT)
                ->count(),
            'absent_today' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_ABSENT)
                ->count(),
            'late_today' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_LATE)
                ->count(),
            'on_leave_today' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_ON_LEAVE)
                ->count(),
        ];
    }
}

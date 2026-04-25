<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;

class GetAttendanceAnalyticsAction
{
    /**
     * Execute the analytics query.
     *
     * @param array $filters
     * @return array
     */
    public function execute(array $filters = []): array
    {
        $startDate = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])
            : now()->startOfMonth();

        $endDate = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])
            : now();

        $departmentId = $filters['department_id'] ?? null;
        $employeeId = $filters['employee_id'] ?? null;

        return [
            'summary' => $this->getSummaryStats($startDate, $endDate, $departmentId, $employeeId),
            'daily_attendance' => $this->getDailyAttendance($startDate, $endDate, $departmentId, $employeeId),
            'status_distribution' => $this->getStatusDistribution($startDate, $endDate, $departmentId, $employeeId),
            'department_stats' => $this->getDepartmentStats($startDate, $endDate, $employeeId),
            'top_employees' => $this->getTopEmployees($startDate, $endDate, $departmentId, $employeeId),
            'work_hours_trend' => $this->getWorkHoursTrend($startDate, $endDate, $departmentId, $employeeId),
            'late_arrivals' => $this->getLateArrivals($startDate, $endDate, $departmentId, $employeeId),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'department_id' => $departmentId,
                'employee_id' => $employeeId,
            ],
        ];
    }

    /**
     * Get summary statistics.
     */
    private function getSummaryStats(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $totalAttendances = (clone $query)->count();
        $presentCount = (clone $query)->where('status', Attendance::STATUS_PRESENT)->count();
        $lateCount = (clone $query)->where('status', Attendance::STATUS_LATE)->count();
        $absentCount = (clone $query)->where('status', Attendance::STATUS_ABSENT)->count();
        $onLeaveCount = (clone $query)->where('status', Attendance::STATUS_ON_LEAVE)->count();

        $avgWorkHours = (clone $query)
            ->whereNotNull('work_hours')
            ->avg('work_hours');

        $totalWorkHours = (clone $query)
            ->whereNotNull('work_hours')
            ->sum('work_hours');

        // Count unique employees who attended
        $uniqueEmployees = (clone $query)
            ->whereNotNull('check_in_time')
            ->distinct('employee_id')
            ->count('employee_id');

        // Total active employees
        $totalEmployeesQuery = Employee::where('status', true);
        if ($departmentId) {
            $totalEmployeesQuery->where('department_id', $departmentId);
        }
        $totalEmployees = $totalEmployeesQuery->count();

        // Attendance rate
        $workingDays = $this->getWorkingDays($startDate, $endDate);
        $expectedAttendances = $totalEmployees * $workingDays;
        $attendanceRate = $expectedAttendances > 0
            ? round(($presentCount + $lateCount) / $expectedAttendances * 100, 1)
            : 0;

        return [
            'total_records' => $totalAttendances,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'on_leave' => $onLeaveCount,
            'avg_work_hours' => round($avgWorkHours ?? 0, 2),
            'total_work_hours' => round($totalWorkHours, 2),
            'unique_employees' => $uniqueEmployees,
            'total_employees' => $totalEmployees,
            'attendance_rate' => $attendanceRate,
            'working_days' => $workingDays,
        ];
    }

    /**
     * Get daily attendance data for charts.
     */
    private function getDailyAttendance(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select(
                DB::raw('DATE(attendance_date) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw('AVG(work_hours) as avg_hours')
            )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->orderBy('date');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $data = $query->get();

        // Fill in missing dates
        $result = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $dayData = $data->firstWhere('date', $dateStr);

            $result[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'total' => $dayData->total ?? 0,
                'present' => $dayData->present ?? 0,
                'late' => $dayData->late ?? 0,
                'absent' => $dayData->absent ?? 0,
                'avg_hours' => round($dayData->avg_hours ?? 0, 2),
            ];
        }

        return $result;
    }

    /**
     * Get status distribution.
     */
    private function getStatusDistribution(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('status')
            ->groupBy('status');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $data = $query->get();

        $statuses = Attendance::getStatuses();
        $result = [];

        foreach ($statuses as $key => $label) {
            $item = $data->firstWhere('status', $key);
            $result[] = [
                'status' => $key,
                'label' => $label,
                'count' => $item->count ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get department statistics.
     */
    private function getDepartmentStats(Carbon $startDate, Carbon $endDate, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select(
                'department_id',
                DB::raw('COUNT(*) as total_attendance'),
                DB::raw("SUM(CASE WHEN status = 'present' OR status = 'late' THEN 1 ELSE 0 END) as present_count"),
                DB::raw('AVG(work_hours) as avg_hours'),
                DB::raw('SUM(work_hours) as total_hours')
            )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('department_id')
            ->groupBy('department_id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->with('department:id,name,code')
            ->orderByDesc('present_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'department_id' => $item->department_id,
                    'department_name' => $item->department?->name ?? 'Unknown',
                    'department_code' => $item->department?->code,
                    'total_attendance' => $item->total_attendance,
                    'present_count' => $item->present_count,
                    'avg_hours' => round($item->avg_hours ?? 0, 2),
                    'total_hours' => round($item->total_hours ?? 0, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get top employees by work hours.
     */
    private function getTopEmployees(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as days_worked'),
                DB::raw('SUM(work_hours) as total_hours'),
                DB::raw('AVG(work_hours) as avg_hours'),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count")
            )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('check_in_time')
            ->groupBy('employee_id')
            ->orderByDesc('total_hours')
            ->limit($employeeId ? 1 : 10);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get()
            ->map(function ($item) {
                $employee = Employee::find($item->employee_id);
                return [
                    'employee_id' => $item->employee_id,
                    'employee_name' => $employee?->full_name ?? 'Unknown',
                    'employee_code' => $employee?->employee_code,
                    'avatar_url' => $employee?->avatar_url,
                    'department' => $employee?->department?->name,
                    'days_worked' => $item->days_worked,
                    'total_hours' => round($item->total_hours ?? 0, 2),
                    'avg_hours' => round($item->avg_hours ?? 0, 2),
                    'late_count' => $item->late_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get work hours trend (weekly averages).
     */
    private function getWorkHoursTrend(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select(
                DB::raw('YEAR(attendance_date) as year'),
                DB::raw('WEEK(attendance_date) as week'),
                DB::raw('AVG(work_hours) as avg_hours'),
                DB::raw('SUM(work_hours) as total_hours'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('work_hours')
            ->groupBy(DB::raw('YEAR(attendance_date)'), DB::raw('WEEK(attendance_date)'))
            ->orderBy('year')
            ->orderBy('week');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get()
            ->map(function ($item) {
                $weekStart = Carbon::now()
                    ->setISODate($item->year, $item->week)
                    ->startOfWeek();

                return [
                    'week' => "Week {$item->week}",
                    'week_start' => $weekStart->toDateString(),
                    'avg_hours' => round($item->avg_hours, 2),
                    'total_hours' => round($item->total_hours, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get late arrivals statistics.
     */
    private function getLateArrivals(Carbon $startDate, Carbon $endDate, ?int $departmentId, ?int $employeeId = null): array
    {
        $query = Attendance::query()
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as late_count')
            )
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('status', Attendance::STATUS_LATE)
            ->groupBy('employee_id')
            ->orderByDesc('late_count')
            ->limit($employeeId ? 1 : 10);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get()
            ->map(function ($item) {
                $employee = Employee::find($item->employee_id);
                return [
                    'employee_id' => $item->employee_id,
                    'employee_name' => $employee?->full_name ?? 'Unknown',
                    'employee_code' => $employee?->employee_code,
                    'avatar_url' => $employee?->avatar_url,
                    'department' => $employee?->department?->name,
                    'late_count' => $item->late_count,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate working days (excluding weekends).
     */
    private function getWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}

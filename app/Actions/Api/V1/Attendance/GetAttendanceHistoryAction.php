<?php

namespace Modules\Employee\Actions\Api\V1\Attendance;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\AttendanceResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;

class GetAttendanceHistoryAction
{
    /**
     * Get attendance history for the authenticated employee.
     */
    public function execute(User $user, ?string $startDate = null, ?string $endDate = null, int $perPage = 15): array
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        $query = Attendance::with(['scans', 'department', 'school'])
            ->where('employee_id', $employee->id)
            ->orderBy('attendance_date', 'desc');

        // Apply date filters
        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('attendance_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('attendance_date', '<=', $endDate);
        }

        $attendances = $query->paginate($perPage);

        // Calculate statistics
        $stats = $this->calculateStats($employee->id, $startDate, $endDate);

        return [
            'success' => true,
            'stats' => $stats,
            'data' => AttendanceResource::collection($attendances)->response()->getData(true),
        ];
    }

    /**
     * Calculate attendance statistics.
     */
    private function calculateStats(int $employeeId, ?string $startDate, ?string $endDate): array
    {
        $query = Attendance::where('employee_id', $employeeId);

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        } else {
            // Default to current month
            $query->whereMonth('attendance_date', now()->month)
                ->whereYear('attendance_date', now()->year);
        }

        $total = $query->count();
        $present = (clone $query)->where('status', Attendance::STATUS_PRESENT)->count();
        $late = (clone $query)->where('status', Attendance::STATUS_LATE)->count();
        $absent = (clone $query)->where('status', Attendance::STATUS_ABSENT)->count();
        $onLeave = (clone $query)->where('status', Attendance::STATUS_ON_LEAVE)->count();

        $totalWorkHours = (clone $query)->sum('work_hours');

        return [
            'total_days' => $total,
            'present_days' => $present,
            'late_days' => $late,
            'absent_days' => $absent,
            'leave_days' => $onLeave,
            'total_work_hours' => round($totalWorkHours, 2),
            'average_work_hours' => $total > 0 ? round($totalWorkHours / $total, 2) : 0,
        ];
    }
}

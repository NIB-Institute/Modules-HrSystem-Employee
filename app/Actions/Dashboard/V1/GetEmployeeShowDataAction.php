<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Carbon\Carbon;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;

class GetEmployeeShowDataAction
{
    public function execute(Employee $employee): array
    {
        // Load relationships so they appear in the resource
        $employee->load([
            'school',
            'department',
            'employeeType',
            'user',
            'familyMembers',
            'academicLevels',
            'foreignLanguages',
            'jobExperiences',
        ]);

        // Get attendance statistics for this employee
        $attendanceStats = $this->getAttendanceStats($employee->id);

        return [
            'employee' => (new EmployeeResource($employee))->resolve(),
            'attendanceStats' => $attendanceStats,
        ];
    }

    private function getAttendanceStats(int $employeeId): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // This month stats
        $monthlyQuery = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', '>=', $thisMonth);

        $monthlyTotal = (clone $monthlyQuery)->count();
        $monthlyPresent = (clone $monthlyQuery)->where('status', Attendance::STATUS_PRESENT)->count();
        $monthlyLate = (clone $monthlyQuery)->where('status', Attendance::STATUS_LATE)->count();
        $monthlyAbsent = (clone $monthlyQuery)->where('status', Attendance::STATUS_ABSENT)->count();
        $monthlyOnLeave = (clone $monthlyQuery)->where('status', Attendance::STATUS_ON_LEAVE)->count();
        $monthlyWorkHours = (clone $monthlyQuery)->whereNotNull('work_hours')->sum('work_hours');

        // This year stats
        $yearlyQuery = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', '>=', $thisYear);

        $yearlyTotal = (clone $yearlyQuery)->count();
        $yearlyPresent = (clone $yearlyQuery)->where('status', Attendance::STATUS_PRESENT)->count();
        $yearlyLate = (clone $yearlyQuery)->where('status', Attendance::STATUS_LATE)->count();
        $yearlyWorkHours = (clone $yearlyQuery)->whereNotNull('work_hours')->sum('work_hours');

        // All time stats
        $allTimeQuery = Attendance::where('employee_id', $employeeId);
        $allTimeTotal = (clone $allTimeQuery)->count();
        $allTimeWorkHours = (clone $allTimeQuery)->whereNotNull('work_hours')->sum('work_hours');

        // Last 5 attendance records
        $recentAttendance = Attendance::where('employee_id', $employeeId)
            ->with('department')
            ->latest('attendance_date')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'uuid' => $a->uuid,
                'date' => $a->attendance_date?->format('M d, Y'),
                'status' => $a->status,
                'status_label' => Attendance::getStatuses()[$a->status] ?? $a->status,
                'check_in' => $a->check_in_time?->format('H:i'),
                'check_out' => $a->check_out_time?->format('H:i'),
                'work_hours' => $a->getFormattedWorkHours(),
            ]);

        return [
            'this_month' => [
                'total' => $monthlyTotal,
                'present' => $monthlyPresent,
                'late' => $monthlyLate,
                'absent' => $monthlyAbsent,
                'on_leave' => $monthlyOnLeave,
                'work_hours' => round($monthlyWorkHours, 2),
                'work_hours_formatted' => $this->formatHours($monthlyWorkHours),
            ],
            'this_year' => [
                'total' => $yearlyTotal,
                'present' => $yearlyPresent,
                'late' => $yearlyLate,
                'work_hours' => round($yearlyWorkHours, 2),
                'work_hours_formatted' => $this->formatHours($yearlyWorkHours),
            ],
            'all_time' => [
                'total' => $allTimeTotal,
                'work_hours' => round($allTimeWorkHours, 2),
                'work_hours_formatted' => $this->formatHours($allTimeWorkHours),
            ],
            'recent' => $recentAttendance,
        ];
    }

    private function formatHours(float $hours): string
    {
        $h = floor(abs($hours));
        $m = round((abs($hours) - $h) * 60);
        return sprintf('%dh %dm', $h, $m);
    }
}

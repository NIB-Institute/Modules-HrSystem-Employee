<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Carbon\Carbon;

class GetSelfServiceAttendanceAction
{
    /**
     * Get today's attendance for self-service check-in/check-out.
     */
    public function execute(?int $employeeId = null): array
    {
        $employee = null;
        $todayAttendance = null;

        // If employee_id is provided, get that employee's attendance
        // Otherwise, try to get from authenticated user's linked employee
        if ($employeeId) {
            $employee = Employee::find($employeeId);
        } else {
            // Try to get employee linked to current user
            $user = auth()->user();
            if ($user) {
                $employee = Employee::where('email', $user->email)->first();
            }
        }

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();
        }

        // Calculate work duration if both check-in and check-out exist
        $workDuration = null;
        $workDurationFormatted = null;
        if ($todayAttendance && $todayAttendance->check_in_time && $todayAttendance->check_out_time) {
            $checkIn = Carbon::parse($todayAttendance->check_in_time);
            $checkOut = Carbon::parse($todayAttendance->check_out_time);
            $workDuration = $checkOut->diffInMinutes($checkIn);
            $hours = floor($workDuration / 60);
            $minutes = $workDuration % 60;
            $workDurationFormatted = "{$hours}h {$minutes}m";
        }

        // Determine state
        $canCheckIn = !$todayAttendance || !$todayAttendance->check_in_time;
        $canCheckOut = $todayAttendance && $todayAttendance->check_in_time && !$todayAttendance->check_out_time;
        $isCompleted = $todayAttendance && $todayAttendance->check_in_time && $todayAttendance->check_out_time;

        // Get late threshold from employee type or default to 9:00 AM
        $lateThreshold = '09:00:00';
        if ($employee && $employee->employeeType && $employee->employeeType->time_start) {
            $lateThreshold = $employee->employeeType->time_start;
        }

        return [
            'employee' => $employee ? [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'avatar_url' => $employee->avatar_url,
                'department_name' => $employee->department_name,
                'job_title' => $employee->job_title,
            ] : null,
            'todayAttendance' => $todayAttendance ? [
                'id' => $todayAttendance->id,
                'uuid' => $todayAttendance->uuid,
                'attendance_date' => $todayAttendance->attendance_date,
                'check_in_time' => $todayAttendance->check_in_time,
                'check_out_time' => $todayAttendance->check_out_time,
                'status' => $todayAttendance->status,
                'status_label' => $todayAttendance->status_label,
                'work_hours_formatted' => $workDurationFormatted ?? $todayAttendance->work_hours_formatted,
            ] : null,
            'state' => [
                'canCheckIn' => $canCheckIn,
                'canCheckOut' => $canCheckOut,
                'isCompleted' => $isCompleted,
            ],
            'config' => [
                'lateThreshold' => $lateThreshold,
                'currentDate' => Carbon::now()->format('Y-m-d'),
                'currentTime' => Carbon::now()->format('H:i:s'),
                'timezone' => config('app.timezone'),
            ],
        ];
    }
}

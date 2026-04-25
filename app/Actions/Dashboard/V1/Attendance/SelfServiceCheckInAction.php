<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Carbon\Carbon;

class SelfServiceCheckInAction
{
    /**
     * Process employee self-service check-in.
     */
    public function execute(int $employeeId, array $data = []): array
    {
        $employee = Employee::findOrFail($employeeId);
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'Already checked in today.',
                'attendance' => $existingAttendance,
            ];
        }

        // Determine if late (default threshold: 9:00 AM)
        $lateThreshold = '09:00:00';
        if ($employee->employeeType && $employee->employeeType->time_start) {
            $lateThreshold = $employee->employeeType->time_start;
        }

        $thresholdTime = Carbon::parse($today . ' ' . $lateThreshold);
        $isLate = $now->gt($thresholdTime);
        $status = $isLate ? 'late' : 'present';

        // Create or update attendance record
        $attendance = $existingAttendance ?? new Attendance();
        $attendance->fill([
            'employee_id' => $employeeId,
            'attendance_date' => $today,
            'check_in_time' => $now->format('H:i:s'),
            'status' => $status,
            'check_in_method' => $data['method'] ?? 'manual',
            'check_in_location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
            'device_info' => $data['device_info'] ?? request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        // Handle coordinates if provided
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $attendance->check_in_coordinates = [
                'lat' => $data['latitude'],
                'lng' => $data['longitude'],
            ];
        }

        $attendance->save();

        return [
            'success' => true,
            'message' => $isLate
                ? 'Checked in successfully (Late arrival)'
                : 'Checked in successfully',
            'attendance' => [
                'id' => $attendance->id,
                'uuid' => $attendance->uuid,
                'check_in_time' => $attendance->check_in_time,
                'status' => $attendance->status,
                'status_label' => Attendance::getStatuses()[$attendance->status] ?? $attendance->status,
                'is_late' => $isLate,
            ],
        ];
    }
}

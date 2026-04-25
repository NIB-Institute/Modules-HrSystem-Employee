<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Modules\Employee\Models\Attendance;
use Carbon\Carbon;

class SelfServiceCheckOutAction
{
    /**
     * Process employee self-service check-out.
     */
    public function execute(int $employeeId, array $data = []): array
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // Find today's attendance record
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'No check-in record found for today.',
                'attendance' => null,
            ];
        }

        if (!$attendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'You must check in before checking out.',
                'attendance' => null,
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'success' => false,
                'message' => 'Already checked out today.',
                'attendance' => $attendance,
            ];
        }

        // Calculate work duration
        $checkIn = Carbon::parse($attendance->attendance_date . ' ' . $attendance->check_in_time);
        $workMinutes = $now->diffInMinutes($checkIn);
        $workHours = round($workMinutes / 60, 2);

        // Update attendance record
        $attendance->check_out_time = $now->format('H:i:s');
        $attendance->check_out_method = $data['method'] ?? 'manual';
        $attendance->check_out_location = $data['location'] ?? null;
        $attendance->work_hours = $workHours;

        // Handle coordinates if provided
        if (isset($data['latitude']) && isset($data['longitude'])) {
            $attendance->check_out_coordinates = [
                'lat' => $data['latitude'],
                'lng' => $data['longitude'],
            ];
        }

        // Update notes if provided
        if (isset($data['notes'])) {
            $attendance->notes = $attendance->notes
                ? $attendance->notes . "\n" . $data['notes']
                : $data['notes'];
        }

        $attendance->save();

        // Format work duration
        $hours = floor($workMinutes / 60);
        $minutes = $workMinutes % 60;
        $workDurationFormatted = "{$hours}h {$minutes}m";

        return [
            'success' => true,
            'message' => "Checked out successfully. Worked: {$workDurationFormatted}",
            'attendance' => [
                'id' => $attendance->id,
                'uuid' => $attendance->uuid,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'status' => $attendance->status,
                'status_label' => Attendance::getStatuses()[$attendance->status] ?? $attendance->status,
                'work_hours' => $workHours,
                'work_hours_formatted' => $workDurationFormatted,
            ],
        ];
    }
}

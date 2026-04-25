<?php

namespace Modules\Employee\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::with(['school', 'department'])->where('status', true)->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();

        $statuses = [
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_LATE,
            Attendance::STATUS_ABSENT,
            Attendance::STATUS_ON_LEAVE,
        ];

        $methods = [
            Attendance::METHOD_QR_SCAN,
            Attendance::METHOD_QR_SCAN,
            Attendance::METHOD_QR_SCAN,
            Attendance::METHOD_MANUAL,
            Attendance::METHOD_BIOMETRIC,
        ];

        $attendanceCount = 0;

        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip if attendance already exists for this employee on this date
                $existingAttendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $currentDate)
                    ->exists();

                if ($existingAttendance) {
                    $currentDate->addDay();
                    continue;
                }

                $status = $statuses[array_rand($statuses)];
                $method = $methods[array_rand($methods)];

                // Generate check-in time (7:30 - 9:30 AM range)
                $checkInHour = rand(7, 9);
                $checkInMinute = rand(0, 59);
                $checkInTime = sprintf('%02d:%02d:00', $checkInHour, $checkInMinute);

                // If late, check-in after 9:00 AM
                if ($status === Attendance::STATUS_LATE) {
                    $checkInHour = rand(9, 10);
                    $checkInMinute = rand(1, 59);
                    $checkInTime = sprintf('%02d:%02d:00', $checkInHour, $checkInMinute);
                }

                // Generate check-out time (4:30 - 6:30 PM range)
                $checkOutHour = rand(16, 18);
                $checkOutMinute = rand(0, 59);
                $checkOutTime = sprintf('%02d:%02d:00', $checkOutHour, $checkOutMinute);

                // For absent or on leave, no check-in/out times
                if (in_array($status, [Attendance::STATUS_ABSENT, Attendance::STATUS_ON_LEAVE])) {
                    $checkInTime = null;
                    $checkOutTime = null;
                }

                // For early leave
                if ($status === Attendance::STATUS_EARLY_LEAVE) {
                    $checkOutHour = rand(14, 16);
                    $checkOutTime = sprintf('%02d:%02d:00', $checkOutHour, $checkOutMinute);
                }

                $notes = null;
                if ($status === Attendance::STATUS_ON_LEAVE) {
                    $leaveReasons = [
                        'Annual leave',
                        'Sick leave',
                        'Personal leave',
                        'Family emergency',
                        'Medical appointment',
                    ];
                    $notes = $leaveReasons[array_rand($leaveReasons)];
                } elseif ($status === Attendance::STATUS_ABSENT) {
                    $notes = 'Unexcused absence';
                } elseif ($status === Attendance::STATUS_LATE && rand(0, 1)) {
                    $lateReasons = [
                        'Traffic delay',
                        'Public transport issue',
                        'Personal emergency',
                    ];
                    $notes = $lateReasons[array_rand($lateReasons)];
                }

                $attendanceData = [
                    'uuid' => (string) Str::uuid(),
                    'employee_id' => $employee->id,
                    'school_id' => $employee->school_id,
                    'department_id' => $employee->department_id,
                    'attendance_date' => $currentDate->format('Y-m-d'),
                    'status' => $status,
                    'notes' => $notes,
                    'ip_address' => '192.168.1.' . rand(1, 254),
                ];

                // Only add check-in/out fields when employee actually checked in
                if ($checkInTime) {
                    $attendanceData['check_in_time'] = $checkInTime;
                    $attendanceData['check_in_method'] = $method;
                    $attendanceData['check_in_location'] = 'Main Office';
                }

                if ($checkOutTime) {
                    $attendanceData['check_out_time'] = $checkOutTime;
                    $attendanceData['check_out_method'] = $method;
                    $attendanceData['check_out_location'] = 'Main Office';
                }

                Attendance::create($attendanceData);

                $attendanceCount++;
                $currentDate->addDay();
            }
        }

        $this->command->info("Created {$attendanceCount} attendance records for " . $employees->count() . ' employees.');
    }
}

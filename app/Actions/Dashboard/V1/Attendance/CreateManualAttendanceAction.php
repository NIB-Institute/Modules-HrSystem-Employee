<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;

class CreateManualAttendanceAction
{
    public function execute(array $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::findOrFail($data['employee_id']);

            // Check for existing attendance on the same date
            $existing = Attendance::where('employee_id', $data['employee_id'])
                ->whereDate('attendance_date', $data['attendance_date'])
                ->first();

            if ($existing) {
                throw new \Exception('Attendance record already exists for this employee on this date.');
            }

            $attendance = Attendance::create([
                'employee_id' => $data['employee_id'],
                'school_id' => $employee->school_id,
                'department_id' => $data['department_id'] ?? $employee->department_id,
                'classroom_id' => $data['classroom_id'] ?? null,
                'attendance_date' => $data['attendance_date'],
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'status' => $data['status'],
                'check_in_method' => Attendance::METHOD_MANUAL,
                'check_out_method' => isset($data['check_out_time']) ? Attendance::METHOD_MANUAL : null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $attendance->fresh(['employee', 'department', 'classroom']);
        });
    }
}

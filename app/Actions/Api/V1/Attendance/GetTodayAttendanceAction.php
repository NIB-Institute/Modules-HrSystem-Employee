<?php

namespace Modules\Employee\Actions\Api\V1\Attendance;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\AttendanceResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;

class GetTodayAttendanceAction
{
    /**
     * Get today's attendance for the authenticated employee.
     */
    public function execute(User $user): array
    {
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        $attendance = Attendance::with(['scans', 'department', 'school'])
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();

        return [
            'success' => true,
            'has_checked_in' => $attendance?->hasCheckedIn() ?? false,
            'has_checked_out' => $attendance?->hasCheckedOut() ?? false,
            'attendance' => $attendance ? new AttendanceResource($attendance) : null,
        ];
    }
}

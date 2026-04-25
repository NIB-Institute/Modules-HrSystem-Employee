<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Attendance;

class UpdateAttendanceAction
{
    public function execute(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data) {
            $attendance->update([
                'check_in_time' => $data['check_in_time'] ?? $attendance->check_in_time,
                'check_out_time' => $data['check_out_time'] ?? $attendance->check_out_time,
                'status' => $data['status'] ?? $attendance->status,
                'notes' => $data['notes'] ?? $attendance->notes,
            ]);

            return $attendance->fresh(['employee', 'department', 'classroom']);
        });
    }
}

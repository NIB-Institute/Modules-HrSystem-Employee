<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Modules\Employee\Models\Attendance;

class GetAttendanceShowDataAction
{
    public function execute(Attendance $attendance): array
    {
        $attendance->load(['employee.user', 'school', 'department', 'classroom']);

        return [
            'attendance' => $attendance,
            'statuses' => Attendance::getStatuses(),
            'methods' => Attendance::getMethods(),
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Attendance;

class BulkDeleteAttendancesAction
{
    public function execute(array $uuids): array
    {
        $deleted = 0;
        $failed = 0;

        DB::transaction(function () use ($uuids, &$deleted, &$failed) {
            foreach ($uuids as $uuid) {
                $attendance = Attendance::where('uuid', $uuid)->first();

                if ($attendance) {
                    $attendance->delete();
                    $deleted++;
                } else {
                    $failed++;
                }
            }
        });

        return [
            'deleted' => $deleted,
            'failed' => $failed,
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Employee;

class BulkDeleteEmployeesAction
{
    public function execute(array $uuids): array
    {
        $deleted = 0;
        $failed = 0;

        DB::transaction(function () use ($uuids, &$deleted, &$failed) {
            foreach ($uuids as $uuid) {
                $employee = Employee::where('uuid', $uuid)->first();

                if ($employee) {
                    $employee->delete();
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

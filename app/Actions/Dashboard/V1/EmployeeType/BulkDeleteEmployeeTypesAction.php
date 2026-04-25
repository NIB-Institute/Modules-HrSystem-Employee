<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\EmployeeType;

class BulkDeleteEmployeeTypesAction
{
    public function execute(array $uuids): array
    {
        $deleted = 0;
        $failed = 0;

        DB::transaction(function () use ($uuids, &$deleted, &$failed) {
            foreach ($uuids as $uuid) {
                $employeeType = EmployeeType::where('uuid', $uuid)->first();

                if ($employeeType) {
                    $employeeType->delete();
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

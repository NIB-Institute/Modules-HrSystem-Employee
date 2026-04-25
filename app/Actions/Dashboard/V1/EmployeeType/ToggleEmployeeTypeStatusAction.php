<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Models\EmployeeType;

class ToggleEmployeeTypeStatusAction
{
    public function execute(EmployeeType $employeeType): EmployeeType
    {
        $employeeType->update([
            'status' => !$employeeType->status,
        ]);

        return $employeeType->fresh();
    }
}

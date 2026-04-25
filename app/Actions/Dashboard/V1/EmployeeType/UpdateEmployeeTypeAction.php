<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Models\EmployeeType;

class UpdateEmployeeTypeAction
{
    public function execute(EmployeeType $employeeType, array $data): EmployeeType
    {
        $employeeType->update($data);

        return $employeeType->fresh();
    }
}

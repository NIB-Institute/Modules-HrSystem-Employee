<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Models\EmployeeType;

class DeleteEmployeeTypeAction
{
    public function execute(EmployeeType $employeeType): bool
    {
        return $employeeType->delete();
    }
}

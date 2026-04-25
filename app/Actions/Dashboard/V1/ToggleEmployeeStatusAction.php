<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Modules\Employee\Models\Employee;

class ToggleEmployeeStatusAction
{
    public function execute(Employee $employee): Employee
    {
        $employee->status = !$employee->status;
        $employee->save();

        return $employee;
    }
}

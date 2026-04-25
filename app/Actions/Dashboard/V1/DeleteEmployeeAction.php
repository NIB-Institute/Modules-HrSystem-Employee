<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Modules\Employee\Models\Employee;

class DeleteEmployeeAction
{
    public function execute(Employee $employee): bool
    {
        return $employee->delete();
    }
}

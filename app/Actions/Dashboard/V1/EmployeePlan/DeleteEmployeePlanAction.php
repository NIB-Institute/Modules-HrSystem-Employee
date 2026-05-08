<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Models\EmployeePlan;

class DeleteEmployeePlanAction
{
    public function execute(EmployeePlan $employeePlan): bool
    {
        return (bool) $employeePlan->delete();
    }
}

<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Models\EmployeePlan;

class UpdateEmployeePlanAction
{
    public function execute(EmployeePlan $employeePlan, array $data): EmployeePlan
    {
        $employeePlan->update($data);

        return $employeePlan->fresh();
    }
}

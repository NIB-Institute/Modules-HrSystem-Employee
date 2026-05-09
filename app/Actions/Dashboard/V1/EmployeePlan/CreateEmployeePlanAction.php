<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Models\EmployeePlan;

class CreateEmployeePlanAction
{
    public function execute(array $data): EmployeePlan
    {
        return EmployeePlan::create($data);
    }
}

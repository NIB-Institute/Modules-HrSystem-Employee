<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeTypeResource;
use Modules\Employee\Models\EmployeeType;

class GetEmployeeTypeShowDataAction
{
    public function execute(EmployeeType $employeeType): array
    {
        $employeeType->loadCount('employees');

        return [
            'employeeType' => (new EmployeeTypeResource($employeeType))->resolve(),
        ];
    }
}

<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeTypeResource;
use Modules\Employee\Models\EmployeeType;

class GetEmployeeTypeEditDataAction
{
    public function execute(EmployeeType $employeeType): array
    {
        return [
            'employeeType' => (new EmployeeTypeResource($employeeType))->resolve(),
        ];
    }
}

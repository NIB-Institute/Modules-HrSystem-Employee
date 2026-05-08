<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Modules\Employee\Models\EmployeeAvailability;

class CreateEmployeeAvailabilityAction
{
    public function execute(array $data): EmployeeAvailability
    {
        return EmployeeAvailability::create($data);
    }
}

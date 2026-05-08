<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Modules\Employee\Models\EmployeeAvailability;

class UpdateEmployeeAvailabilityAction
{
    public function execute(EmployeeAvailability $employeeAvailability, array $data): EmployeeAvailability
    {
        $employeeAvailability->update($data);

        return $employeeAvailability->fresh();
    }
}

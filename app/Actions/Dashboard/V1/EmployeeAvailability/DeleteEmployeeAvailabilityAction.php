<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability;

use Modules\Employee\Models\EmployeeAvailability;

class DeleteEmployeeAvailabilityAction
{
    public function execute(EmployeeAvailability $employeeAvailability): bool
    {
        return (bool) $employeeAvailability->delete();
    }
}

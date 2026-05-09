<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Models\EmployeePlanAssignment;

class DeleteEmployeePlanAssignmentAction
{
    public function execute(EmployeePlanAssignment $assignment): bool
    {
        return (bool) $assignment->delete();
    }
}

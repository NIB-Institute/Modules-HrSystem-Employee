<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanAssignmentResource;
use Modules\Employee\Models\EmployeePlanAssignment;

class GetEmployeePlanAssignmentShowDataAction
{
    public function execute(EmployeePlanAssignment $assignment): array
    {
        $assignment->load(['plan', 'employee', 'availability']);

        return [
            'assignment' => (new EmployeePlanAssignmentResource($assignment))->resolve(),
        ];
    }
}

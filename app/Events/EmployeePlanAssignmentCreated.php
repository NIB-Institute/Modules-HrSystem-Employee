<?php

namespace Modules\Employee\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Employee\Models\EmployeePlanAssignment;

class EmployeePlanAssignmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly EmployeePlanAssignment $assignment)
    {
    }
}

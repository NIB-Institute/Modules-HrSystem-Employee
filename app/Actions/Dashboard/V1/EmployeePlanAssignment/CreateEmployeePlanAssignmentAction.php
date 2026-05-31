<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeePlanAssignmentCreated;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class CreateEmployeePlanAssignmentAction
{
    public function execute(array $data): EmployeePlanAssignment
    {
        $data['assigned_at'] ??= now();
        $data['status'] ??= EmployeePlanAssignmentEnum::STATUS_ASSIGNED;

        $assignment = EmployeePlanAssignment::create($data);

        // If the caller already marked it completed, derive expires_at from plan.valid_for_months.
        if (
            $assignment->status === EmployeePlanAssignmentEnum::STATUS_COMPLETED
            && $assignment->completed_at
            && empty($assignment->expires_at)
        ) {
            $plan = EmployeePlan::find($assignment->employee_plan_id);
            if ($plan?->valid_for_months) {
                $assignment->update([
                    'expires_at' => $assignment->completed_at->copy()->addMonths($plan->valid_for_months),
                ]);
            }
        }

        $fresh = $assignment->fresh();

        // Post on-assignment alert to the plan's Telegram group (queued).
        EmployeePlanAssignmentCreated::dispatch($fresh);

        return $fresh;
    }
}

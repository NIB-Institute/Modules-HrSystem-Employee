<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Models\EmployeePlanAssignment;

class UpdateEmployeePlanAssignmentAction
{
    public function execute(EmployeePlanAssignment $assignment, array $data): EmployeePlanAssignment
    {
        $previousStatus = $assignment->status;

        $assignment->update($data);

        // If transitioning to completed, set completed_at and derive expires_at.
        if (
            ($data['status'] ?? null) === EmployeePlanAssignmentEnum::STATUS_COMPLETED
            && $previousStatus !== EmployeePlanAssignmentEnum::STATUS_COMPLETED
        ) {
            $plan = $assignment->plan;
            $assignment->update([
                'completed_at' => $assignment->completed_at ?? now(),
                'expires_at' => $plan?->valid_for_months
                    ? ($assignment->completed_at ?? now())->copy()->addMonths($plan->valid_for_months)
                    : $assignment->expires_at,
            ]);
        }

        return $assignment->fresh();
    }
}

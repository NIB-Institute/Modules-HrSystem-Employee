<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeesAssignedToPlan;
use Modules\Employee\Models\EmployeePlanAssignment;

class BulkAssignEmployeePlanAction
{
    /**
     * Assign multiple employees to one plan in a single transaction.
     * Skips employees who already have an active (non-soft-deleted) assignment.
     *
     * @param  array<int, int>  $employeeIds
     * @return array{created: int, skipped: int}
     */
    public function execute(int $planId, array $employeeIds, ?string $notes = null): array
    {
        $existing = EmployeePlanAssignment::query()
            ->where('employee_plan_id', $planId)
            ->whereIn('employee_id', $employeeIds)
            ->pluck('employee_id')
            ->all();

        $toCreate = array_values(array_diff($employeeIds, $existing));
        $skipped = count($employeeIds) - count($toCreate);

        $created = 0;
        $newAssignmentIds = [];

        DB::transaction(function () use ($planId, $toCreate, $notes, &$created, &$newAssignmentIds) {
            foreach ($toCreate as $employeeId) {
                $assignment = EmployeePlanAssignment::create([
                    'employee_plan_id' => $planId,
                    'employee_id' => $employeeId,
                    'status' => EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                    'assigned_at' => now(),
                    'notes' => $notes,
                ]);
                $newAssignmentIds[] = $assignment->id;
                $created++;
            }
        });

        // Dispatch ONE batch event listing all new assignees — listener posts a
        // single consolidated Telegram message (not one per assignee).
        if (! empty($newAssignmentIds)) {
            EmployeesAssignedToPlan::dispatch($planId, $newAssignmentIds);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}

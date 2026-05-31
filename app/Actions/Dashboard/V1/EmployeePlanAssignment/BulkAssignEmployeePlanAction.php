<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeePlanAssignmentCreated;
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
        $newAssignments = [];

        DB::transaction(function () use ($planId, $toCreate, $notes, &$created, &$newAssignments) {
            foreach ($toCreate as $employeeId) {
                $assignment = EmployeePlanAssignment::create([
                    'employee_plan_id' => $planId,
                    'employee_id' => $employeeId,
                    'status' => EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                    'assigned_at' => now(),
                    'notes' => $notes,
                ]);
                $newAssignments[] = $assignment;
                $created++;
            }
        });

        // Dispatch on-assignment event for each new assignment (fires Telegram alert per assignee).
        foreach ($newAssignments as $assignment) {
            EmployeePlanAssignmentCreated::dispatch($assignment);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}

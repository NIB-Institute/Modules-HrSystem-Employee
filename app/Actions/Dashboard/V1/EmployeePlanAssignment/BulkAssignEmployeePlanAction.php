<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeesAssignedToPlan;
use Modules\Employee\Listeners\SendBatchAssignmentNotificationListener;
use Modules\Employee\Models\EmployeePlanAssignment;
use Throwable;

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
        Log::info('BulkAssign: start', ['plan_id' => $planId, 'employee_ids' => $employeeIds]);

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

        Log::info('BulkAssign: created', ['count' => $created, 'skipped' => $skipped, 'ids' => $newAssignmentIds]);

        // Post the Telegram batch message DIRECTLY (no event/queue indirection)
        // so we can't lose the call to a misconfigured listener or stuck queue.
        // The event still fires for any other interested listeners.
        if (! empty($newAssignmentIds)) {
            try {
                $event = new EmployeesAssignedToPlan($planId, $newAssignmentIds);

                // Direct synchronous call — exceptions surface in this request.
                app(SendBatchAssignmentNotificationListener::class)->handle($event);

                Log::info('BulkAssign: telegram broadcast completed', ['plan_id' => $planId]);
            } catch (Throwable $e) {
                Log::error('BulkAssign: telegram broadcast FAILED', [
                    'plan_id' => $planId,
                    'error' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 500),
                ]);
                // Don't fail the whole request — assignments are already saved.
            }

            // Still dispatch the event for any other listeners (e.g. future audit logs).
            EmployeesAssignedToPlan::dispatch($planId, $newAssignmentIds);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}

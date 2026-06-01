<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment;

use Illuminate\Support\Facades\Log;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeePlanAssignmentCreated;
use Modules\Employee\Listeners\SendOnAssignmentReminderListener;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;
use Throwable;

class CreateEmployeePlanAssignmentAction
{
    public function execute(array $data): EmployeePlanAssignment
    {
        Log::info('SingleAssign: start', ['data' => $data]);

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

        Log::info('SingleAssign: created', ['assignment_id' => $fresh->id]);

        // Post on-assignment alert DIRECTLY (no event/queue indirection)
        // so we can't lose the call to a misconfigured listener or stuck queue.
        try {
            $event = new EmployeePlanAssignmentCreated($fresh);
            app(SendOnAssignmentReminderListener::class)->handle($event);
            Log::info('SingleAssign: telegram broadcast completed', ['assignment_id' => $fresh->id]);
        } catch (Throwable $e) {
            Log::error('SingleAssign: telegram broadcast FAILED', [
                'assignment_id' => $fresh->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Still dispatch the event (for any external listeners), but we've
        // already posted to Telegram via the direct call above.
        // EmployeePlanAssignmentCreated::dispatch($fresh);  // disabled to avoid double-send

        return $fresh;
    }
}

<?php

namespace Modules\Employee\Listeners;

use App\Services\Notification\Channels\TelegramChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Events\EmployeesAssignedToPlan;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanAssignment;

/**
 * Posts ONE Telegram alert in the plan's group after a bulk-assign action.
 * The message lists the plan's FULL current team and marks newly-added employees
 * with a "🆕" badge, so HR sees the complete roster regardless of whether some
 * were already assigned.
 *
 * Skipped silently when the plan has no telegram_group_chat_id.
 */
class SendBatchAssignmentNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(private readonly TelegramChannel $telegram)
    {
    }

    public function handle(EmployeesAssignedToPlan $event): void
    {
        if (empty($event->newAssignmentIds)) {
            return;
        }

        $plan = EmployeePlan::find($event->planId);
        if (! $plan) {
            return;
        }

        $chatId = $plan->telegram_group_chat_id;
        if (! $chatId) {
            return;
        }

        // Load the plan's full current team (active assignments + employee).
        $allAssignments = EmployeePlanAssignment::with('employee')
            ->where('employee_plan_id', $plan->id)
            ->whereIn('status', [
                EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS,
            ])
            ->get();

        if ($allAssignments->isEmpty()) {
            return;
        }

        $teamLines = $allAssignments->map(function ($assignment) {
            $employee = $assignment->employee;
            $name = $employee
                ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''))
                : '(unknown)';
            return '  • ' . e($name);
        })->all();

        $prevLocale = App::getLocale();
        App::setLocale('km');
        try {
            $startDate = $plan->start_date?->locale('km')->translatedFormat('D, M j Y') ?? '—';
            $startTime = $plan->start_time ?: '—';
            $location = $plan->location ?: '—';
            $sep = '────────────────────';

            $payload = [
                'title' => '',
                'body' => implode("\n", [
                    '📋 ' . __('employee::plan_reminders.labels.workshop_name') . ':',
                    '<b>' . e($plan->title ?: '—') . '</b>',
                    $sep,
                    '📅 ' . __('employee::plan_reminders.labels.date') . ':     ' . $startDate,
                    '⏰ ' . __('employee::plan_reminders.labels.time') . ':     ' . e($startTime),
                    '📍 ' . __('employee::plan_reminders.labels.location') . ': ' . e($location),
                    $sep,
                    '👥 ' . __('employee::plan_reminders.labels.team') . ' (' . $allAssignments->count() . '):',
                    ...$teamLines,
                    $sep,
                ]),
            ];
        } finally {
            App::setLocale($prevLocale);
        }

        $result = $this->telegram->sendToChannel($chatId, $payload);

        if (! $result->success) {
            Log::warning('SendBatchAssignmentNotificationListener: Telegram send failed', [
                'plan_id' => $plan->id,
                'chat_id' => $chatId,
                'team_size' => $allAssignments->count(),
                'error' => $result->error,
            ]);
        }
    }
}

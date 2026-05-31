<?php

namespace Modules\Employee\Listeners;

use App\Services\Notification\Channels\TelegramChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Employee\Events\EmployeePlanAssignmentCreated;

/**
 * Posts a Telegram alert in the plan's group when a new employee is assigned.
 * Fires per-assignment (no dedup needed — each assignment is a discrete event).
 *
 * Skipped silently when the plan has no telegram_group_chat_id.
 */
class SendOnAssignmentReminderListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(private readonly TelegramChannel $telegram)
    {
    }

    public function handle(EmployeePlanAssignmentCreated $event): void
    {
        $assignment = $event->assignment->loadMissing(['plan', 'employee']);

        $plan = $assignment->plan;
        $employee = $assignment->employee;

        if (! $plan || ! $employee) {
            return;
        }

        $chatId = $plan->telegram_group_chat_id;
        if (! $chatId) {
            return; // Plan has no group configured — silently skip.
        }

        $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'An employee';

        $bi = fn (string $key): string => trans($key, [], 'en') . ' / ' . trans($key, [], 'km');
        $startDate = $plan->start_date?->translatedFormat('D, M j Y') ?? '—';
        $startTime = $plan->start_time ?: '—';
        $location = $plan->location ?: '—';
        $sep = '────────────────────';

        $payload = [
            'title' => '',
            'body' => implode("\n", [
                '📋 ' . $bi('employee::plan_reminders.labels.workshop_name') . ':',
                '<b>' . e($plan->title ?: '—') . '</b>',
                $sep,
                '📅 ' . $bi('employee::plan_reminders.labels.date') . ':     ' . $startDate,
                '⏰ ' . $bi('employee::plan_reminders.labels.time') . ':     ' . e($startTime),
                '📍 ' . $bi('employee::plan_reminders.labels.location') . ': ' . e($location),
                $sep,
                '👥 ' . $bi('employee::plan_reminders.labels.team') . ' (1):',
                '  • ' . e($employeeName),
                $sep,
            ]),
        ];

        $result = $this->telegram->sendToChannel($chatId, $payload);

        if (! $result->success) {
            Log::warning('SendOnAssignmentReminderListener: Telegram send failed', [
                'assignment_id' => $assignment->id,
                'plan_id' => $plan->id,
                'chat_id' => $chatId,
                'error' => $result->error,
            ]);
        }
    }
}

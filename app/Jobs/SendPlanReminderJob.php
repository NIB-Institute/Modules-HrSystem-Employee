<?php

namespace Modules\Employee\Jobs;

use App\Services\Notification\Channels\TelegramChannel;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Enums\EmployeePlanReminderTierEnum;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanReminderLog;
use Throwable;

/**
 * Sends ONE Telegram group message per plan per occurrence per tier.
 * The message is a widget-style card listing plan info + all currently-assigned employees.
 *
 * Idempotency: unique (employee_plan_id, occurrence_date, tier) index.
 */
class SendPlanReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(
        public readonly int $planId,
        public readonly string $occurrenceDate,
        public readonly string $tier,
    ) {
    }

    public function handle(TelegramChannel $telegram): void
    {
        $tier = EmployeePlanReminderTierEnum::from($this->tier);

        $log = $this->createLogOrAbort();
        if ($log === null) {
            return; // Duplicate; already sent or in-flight from a parallel worker.
        }

        try {
            $plan = EmployeePlan::with(['assignments' => function ($q) {
                $q->whereIn('status', [
                    EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                    EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS,
                ])->with('employee');
            }])->find($this->planId);

            if (! $plan) {
                $this->markSkipped($log, 'Plan no longer exists.');
                return;
            }

            $chatId = $plan->telegram_group_chat_id;
            if (! $chatId) {
                $this->markSkipped($log, 'Plan has no telegram_group_chat_id.');
                return;
            }

            $assignees = $plan->assignments
                ->pluck('employee')
                ->filter()
                ->values();

            if ($assignees->isEmpty()) {
                $this->markSkipped($log, 'No active assignees.');
                return;
            }

            $payload = $this->buildPayload($tier, $plan, $assignees->all());
            $result = $telegram->sendToChannel($chatId, $payload);

            if ($result->success) {
                $log->update([
                    'status' => EmployeePlanReminderLog::STATUS_SENT,
                    'telegram_message_id' => $result->messageId,
                    'recipient_count' => $assignees->count(),
                    'sent_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => EmployeePlanReminderLog::STATUS_FAILED,
                    'error' => $result->error ?? 'Unknown Telegram failure',
                    'recipient_count' => $assignees->count(),
                ]);
            }
        } catch (Throwable $e) {
            $log->update([
                'status' => EmployeePlanReminderLog::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
            Log::error('SendPlanReminderJob failed', [
                'plan_id' => $this->planId,
                'tier' => $this->tier,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Trigger queue retry per $tries.
        }
    }

    private function createLogOrAbort(): ?EmployeePlanReminderLog
    {
        return DB::transaction(function () {
            $existing = EmployeePlanReminderLog::where([
                'employee_plan_id' => $this->planId,
                'occurrence_date' => $this->occurrenceDate,
                'tier' => $this->tier,
            ])->lockForUpdate()->first();

            if ($existing) {
                return null;
            }

            return EmployeePlanReminderLog::create([
                'employee_plan_id' => $this->planId,
                'occurrence_date' => $this->occurrenceDate,
                'tier' => $this->tier,
                'channel' => 'telegram',
                'status' => EmployeePlanReminderLog::STATUS_SENT,
            ]);
        });
    }

    private function markSkipped(EmployeePlanReminderLog $log, string $reason): void
    {
        $log->update([
            'status' => EmployeePlanReminderLog::STATUS_SKIPPED,
            'error' => $reason,
        ]);
    }

    /**
     * Build the widget-style payload. TelegramChannel uses HTML parse_mode
     * and wraps `title` in <b>...</b>; `body` is sent raw, so HTML is fine here.
     */
    private function buildPayload(EmployeePlanReminderTierEnum $tier, EmployeePlan $plan, array $assignees): array
    {
        $occurrence = CarbonImmutable::parse($this->occurrenceDate);

        $key = "employee::plan_reminders.{$tier->value}";

        $names = array_map(
            static fn ($e) => '  • ' . trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')),
            $assignees,
        );

        $timeRange = $plan->start_time
            ? ($plan->end_time ? "{$plan->start_time} – {$plan->end_time}" : (string) $plan->start_time)
            : '—';

        $prevLocale = App::getLocale();
        App::setLocale('km');
        try {
            $dateStr = $occurrence->locale('km')->translatedFormat('D, M j Y');
            $headerEmoji = (string) __("{$key}.header_emoji") ?: '📋';

            $sep = '────────────────────';

            $bodyLines = [
                '<b>' . $headerEmoji . ' ' . e(__("{$key}.title")) . '</b>',
                $sep,
                '📋 ' . __('employee::plan_reminders.labels.workshop_name') . ':',
                '<b>' . e($plan->title ?: '—') . '</b>',
                $sep,
                '📅 ' . __('employee::plan_reminders.labels.date') . ':     ' . $dateStr,
                '⏰ ' . __('employee::plan_reminders.labels.time') . ':     ' . e($timeRange),
                '📍 ' . __('employee::plan_reminders.labels.location') . ': ' . e($plan->location ?: '—'),
                $sep,
                '👥 ' . __('employee::plan_reminders.labels.team') . ' (' . count($assignees) . '):',
                ...$names,
                $sep,
                (string) __("{$key}.footer"),
            ];

            return [
                'title' => '',
                'body' => implode("\n", $bodyLines),
            ];
        } finally {
            App::setLocale($prevLocale);
        }
    }
}

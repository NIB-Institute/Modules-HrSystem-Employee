<?php

namespace Modules\Employee\Console\Commands;

use App\Services\Notification\Channels\TelegramChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Enums\EmployeePlanReminderTierEnum;
use Modules\Employee\Jobs\SendPlanReminderJob;
use Modules\Employee\Models\EmployeePlan;
use Modules\Employee\Models\EmployeePlanReminderLog;

/**
 * Sends a test reminder message to the Telegram group configured on a plan,
 * bypassing the schedule fire-window. Useful for verifying chat_id + bot setup.
 *
 * Usage:
 *   php artisan employee:telegram:test-plan-message
 *     -> picks the first plan that has a telegram_group_chat_id set
 *
 *   php artisan employee:telegram:test-plan-message {id}
 *     -> targets the plan with the given numeric ID (or UUID also accepted)
 */
class SendTestPlanMessageCommand extends Command
{
    protected $signature = 'employee:telegram:test-plan-message {plan?} {--tier= : group_3d or group_1d to render the actual reminder payload}';

    protected $description = 'Send a test reminder message to a plan\'s configured Telegram group (skips schedule window). Pass plan id or uuid. Use --tier=group_3d|group_1d to send the real reminder format.';

    public function handle(TelegramChannel $telegram): int
    {
        $identifier = $this->argument('plan');
        $tierOption = $this->option('tier');

        if ($identifier === null) {
            $plan = EmployeePlan::whereNotNull('telegram_group_chat_id')->first();
        } elseif (ctype_digit((string) $identifier)) {
            $plan = EmployeePlan::find((int) $identifier);
        } else {
            $plan = EmployeePlan::where('uuid', $identifier)->first();
        }

        if (! $plan) {
            $this->error($identifier === null
                ? 'No plan with a telegram_group_chat_id was found. Set one on a plan first.'
                : "No plan found with id/uuid: {$identifier}");
            return self::FAILURE;
        }

        if (! $plan->telegram_group_chat_id) {
            $this->error("Plan \"{$plan->title}\" has no telegram_group_chat_id set.");
            return self::FAILURE;
        }

        // --tier path: dispatch the real reminder Job so we use the actual
        // reminder payload (bilingual title, etc.). With QUEUE_CONNECTION=sync
        // this runs immediately.
        if ($tierOption) {
            try {
                $tier = EmployeePlanReminderTierEnum::from($tierOption);
            } catch (\ValueError) {
                $this->error("Invalid --tier value: {$tierOption}. Use group_3d or group_1d.");
                return self::FAILURE;
            }

            $occurrenceDate = ($plan->start_date ?? now())->toDateString();

            // Clear any existing reminder log for this (plan, occurrence, tier) so the
            // Job's idempotency guard doesn't short-circuit our test.
            EmployeePlanReminderLog::where('employee_plan_id', $plan->id)
                ->where('occurrence_date', $occurrenceDate)
                ->where('tier', $tier->value)
                ->delete();

            $this->line("Dispatching SendPlanReminderJob (tier={$tier->value}, occurrence={$occurrenceDate}) for plan \"{$plan->title}\"...");

            SendPlanReminderJob::dispatch($plan->id, $occurrenceDate, $tier->value);

            $this->info('✅ Dispatched. With QUEUE_CONNECTION=sync the message arrived already; otherwise run `php artisan queue:work --once`.');
            return self::SUCCESS;
        }

        $assignees = $plan->assignments()
            ->whereIn('status', [
                EmployeePlanAssignmentEnum::STATUS_ASSIGNED,
                EmployeePlanAssignmentEnum::STATUS_IN_PROGRESS,
            ])
            ->with('employee')
            ->get()
            ->pluck('employee')
            ->filter();

        $names = $assignees->isEmpty()
            ? ['  • (no employees assigned yet)']
            : $assignees->map(fn ($e) => '  • ' . trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')))->all();

        $prevLocale = App::getLocale();
        App::setLocale('km');
        try {
            $sep = '────────────────────';
            $startDate = $plan->start_date?->locale('km')->translatedFormat('D, M j Y') ?? '—';

            $body = implode("\n", [
                '📋 ' . __('employee::plan_reminders.labels.workshop_name') . ':',
                '<b>' . e($plan->title) . '</b>',
                $sep,
                '📅 ' . __('employee::plan_reminders.labels.date') . ':     ' . $startDate,
                '⏰ ' . __('employee::plan_reminders.labels.time') . ':     ' . ($plan->start_time ?: '—'),
                '📍 ' . __('employee::plan_reminders.labels.location') . ': ' . e($plan->location ?: '—'),
                $sep,
                '👥 ' . __('employee::plan_reminders.labels.team') . ' (' . $assignees->count() . '):',
                ...$names,
                $sep,
                (string) __('employee::plan_reminders.footer'),
            ]);
        } finally {
            App::setLocale($prevLocale);
        }

        $this->line("Sending test message to chat_id {$plan->telegram_group_chat_id} for plan \"{$plan->title}\"...");

        $result = $telegram->sendToChannel($plan->telegram_group_chat_id, [
            'title' => '',
            'body' => $body,
        ]);

        if ($result->success) {
            $this->info('✅ Sent! Check your Telegram group.');
            $this->line('Message ID: ' . $result->messageId);
            return self::SUCCESS;
        }

        $this->error('❌ Send failed: ' . $result->error);
        $this->line('Common causes:');
        $this->line('  • Bot is not a member of the group (or was removed)');
        $this->line('  • chat_id is wrong');
        $this->line('  • Bot was blocked');
        return self::FAILURE;
    }
}

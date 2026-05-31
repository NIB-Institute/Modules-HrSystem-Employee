<?php

namespace Modules\Employee\Console\Commands;

use App\Services\Notification\Channels\TelegramChannel;
use Illuminate\Console\Command;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;
use Modules\Employee\Models\EmployeePlan;

/**
 * Sends a test reminder message to the Telegram group configured on a plan,
 * bypassing the schedule fire-window. Useful for verifying chat_id + bot setup.
 *
 * Usage:
 *   php artisan employee:telegram:test-plan-message
 *     -> picks the first plan that has a telegram_group_chat_id set
 *
 *   php artisan employee:telegram:test-plan-message {uuid}
 *     -> targets the plan with the given uuid
 */
class SendTestPlanMessageCommand extends Command
{
    protected $signature = 'employee:telegram:test-plan-message {uuid?}';

    protected $description = 'Send a test reminder message to a plan\'s configured Telegram group (skips schedule window).';

    public function handle(TelegramChannel $telegram): int
    {
        $uuid = $this->argument('uuid');

        $plan = $uuid
            ? EmployeePlan::where('uuid', $uuid)->first()
            : EmployeePlan::whereNotNull('telegram_group_chat_id')->first();

        if (! $plan) {
            $this->error($uuid
                ? "No plan found with uuid: {$uuid}"
                : 'No plan with a telegram_group_chat_id was found. Set one on a plan first.');
            return self::FAILURE;
        }

        if (! $plan->telegram_group_chat_id) {
            $this->error("Plan \"{$plan->title}\" has no telegram_group_chat_id set.");
            return self::FAILURE;
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

        $body = implode("\n", [
            '<b>' . e($plan->title) . '</b>',
            '',
            '📅 Date:     ' . ($plan->start_date?->format('D, M j Y') ?? '—'),
            '⏰ Time:     ' . ($plan->start_time ?: '—'),
            '📍 Location: ' . e($plan->location ?: '—'),
            '',
            '👥 Assigned (' . $assignees->count() . '):',
            ...$names,
            '',
            '(This is a test message sent via php artisan employee:telegram:test-plan-message)',
        ]);

        $this->line("Sending test message to chat_id {$plan->telegram_group_chat_id} for plan \"{$plan->title}\"...");

        $result = $telegram->sendToChannel($plan->telegram_group_chat_id, [
            'title' => '✅ Test reminder',
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

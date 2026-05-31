<?php

namespace Modules\Employee\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Helper for admins to discover chat IDs of Telegram groups/chats the bot is in.
 *
 * How to use:
 *   1. Add the bot to a Telegram group.
 *   2. Run: php artisan employee:telegram:show-chats
 *   3. Copy the chat ID printed for your group and paste it into the plan's
 *      "Telegram Group (Reminders)" field.
 */
class ShowTelegramChatsCommand extends Command
{
    protected $signature = 'employee:telegram:show-chats';

    protected $description = 'List Telegram chats/groups the bot is in (with chat IDs). Run after adding the bot to a group.';

    public function handle(): int
    {
        $token = config('services.telegram.bot_token');

        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env. Set it, then run: php artisan config:clear');
            return self::FAILURE;
        }

        // Step 1: verify token + show which bot it belongs to.
        $me = Http::get("https://api.telegram.org/bot{$token}/getMe");
        if (! $me->successful() || ! $me->json('ok')) {
            $this->error('Bot token in .env is invalid: ' . ($me->json('description') ?? 'getMe failed'));
            return self::FAILURE;
        }
        $botUsername = $me->json('result.username');
        $this->line("Bot token belongs to: @{$botUsername}");

        // Step 2: detect a webhook (it would swallow getUpdates results).
        $webhookInfo = Http::get("https://api.telegram.org/bot{$token}/getWebhookInfo");
        $webhookUrl = $webhookInfo->json('result.url');
        if (! empty($webhookUrl)) {
            $this->warn("A webhook is set on this bot: {$webhookUrl}");
            $this->warn('That webhook is intercepting all updates, so getUpdates is empty.');
            $this->line('Deleting the webhook temporarily so we can read updates...');
            Http::get("https://api.telegram.org/bot{$token}/deleteWebhook");
            $this->line('Webhook deleted. Now please:');
            $this->line("  1. Go to your Telegram group");
            $this->line("  2. Send a NEW message: @{$botUsername} hello");
            $this->line('  3. Run this command again');
            $this->line('');
            $this->warn('NOTE: If you actually use the telegraph webhook for something else, re-register it later with: php artisan telegraph:set-webhook');
            return self::SUCCESS;
        }

        $this->line('Calling Telegram getUpdates...');
        $response = Http::get("https://api.telegram.org/bot{$token}/getUpdates");

        if (! $response->successful() || ! $response->json('ok')) {
            $this->error('Telegram API error: ' . ($response->json('description') ?? $response->body()));
            return self::FAILURE;
        }

        $updates = $response->json('result', []);

        if (empty($updates)) {
            $this->warn('No recent updates found.');
            $this->line('');
            $this->line('Possible reasons:');
            $this->line('  • You added the bot more than ~24 hours ago (Telegram drops old updates)');
            $this->line('  • Nobody mentioned the bot in the group');
            $this->line('');
            $this->line('Fix: in your Telegram group, send this message:');
            $this->line("  @{$botUsername} hello");
            $this->line('then run this command again.');
            return self::SUCCESS;
        }

        // Collect unique chats across all updates (messages + join events).
        $chats = [];
        foreach ($updates as $update) {
            $chat = $update['message']['chat']
                ?? $update['my_chat_member']['chat']
                ?? $update['channel_post']['chat']
                ?? null;

            if ($chat && isset($chat['id'])) {
                $chats[$chat['id']] = [
                    'id' => $chat['id'],
                    'type' => $chat['type'] ?? '?',
                    'title' => $chat['title'] ?? ($chat['username'] ?? $chat['first_name'] ?? '—'),
                ];
            }
        }

        if (empty($chats)) {
            $this->warn('No chat data found in updates.');
            return self::SUCCESS;
        }

        $this->line('');
        $this->info('Chats the bot has seen recently:');
        $this->table(
            ['Chat ID', 'Type', 'Title / Name'],
            array_values($chats),
        );
        $this->line('');
        $this->line('👉 Copy the Chat ID for your group and paste it into the plan\'s "Telegram Group (Reminders)" → "Group Chat ID" field.');

        return self::SUCCESS;
    }
}

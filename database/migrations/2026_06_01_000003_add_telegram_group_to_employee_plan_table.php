<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Telegram group is stored per-plan (not per-school). Each plan has its own group
 * chat where assignment + countdown reminders are posted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_plan', function (Blueprint $table) {
            $table->string('telegram_group_chat_id')->nullable()->after('location');
            $table->string('telegram_group_name')->nullable()->after('telegram_group_chat_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plan', function (Blueprint $table) {
            $table->dropColumn(['telegram_group_chat_id', 'telegram_group_name']);
        });
    }
};

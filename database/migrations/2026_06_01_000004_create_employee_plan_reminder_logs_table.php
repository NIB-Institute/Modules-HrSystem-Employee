<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One reminder log row per (plan, occurrence_date, tier). The unique index
 * makes the hourly scheduler safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_plan_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_plan_id')
                ->constrained('employee_plan')
                ->cascadeOnDelete();
            $table->date('occurrence_date');
            $table->string('tier', 32);
            $table->string('channel', 16)->default('telegram');
            $table->string('status', 16);
            $table->string('telegram_message_id')->nullable();
            $table->text('error')->nullable();
            $table->unsignedSmallInteger('recipient_count')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_plan_id', 'occurrence_date', 'tier'], 'eprl_unique');
            $table->index(['occurrence_date', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_plan_reminder_logs');
    }
};

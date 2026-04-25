<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['leave', 'overtime', 'remote', 'early_leave', 'late_arrival', 'other'])->default('leave');
            $table->text('reason');
            $table->date('from_date');
            $table->date('to_date');
            $table->timestamp('request_date')->useCurrent();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->boolean('rejected_status')->default(false)->comment('Whether request was rejected with status');
            $table->text('rejected_reason')->nullable()->comment('Reason for rejection');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['status', 'request_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};

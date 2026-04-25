<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('school_departments')->onDelete('set null');
            $table->foreignId('classroom_id')->nullable()->constrained('school_classrooms')->onDelete('set null');

            // Attendance details
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_leave', 'half_day', 'on_leave'])->default('present');
            $table->enum('check_in_method', ['qr_scan', 'manual', 'biometric', 'face_recognition'])->default('qr_scan');
            $table->enum('check_out_method', ['qr_scan', 'manual', 'biometric', 'face_recognition'])->nullable();

            // Location tracking
            $table->string('check_in_location', 255)->nullable();
            $table->string('check_out_location', 255)->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();

            // Work hours calculation
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);

            // Additional info
            $table->text('notes')->nullable();
            $table->string('device_info', 255)->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->unique(['employee_id', 'attendance_date'], 'unique_daily_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};

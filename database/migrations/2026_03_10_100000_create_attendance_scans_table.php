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
        Schema::create('attendance_scans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('attendance_id')->constrained('employee_attendances')->onDelete('cascade');

            // Scan type and timing
            $table->enum('scan_type', ['check_in', 'check_out']);
            $table->timestamp('scanned_at');
            $table->string('timezone', 50)->nullable(); // e.g., 'Asia/Phnom_Penh'

            // GPS Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('accuracy', 10, 2)->nullable(); // GPS accuracy in meters
            $table->string('address', 500)->nullable(); // Reverse geocoded address

            // Scan method
            $table->enum('scan_method', ['qr_scan', 'manual', 'biometric', 'face_recognition'])->default('qr_scan');

            // Device and network info
            $table->json('device_info')->nullable(); // { browser, os, device_type, user_agent }
            $table->ipAddress('ip_address')->nullable();

            // Polymorphic location (school, department, classroom, etc.)
            $table->string('location_type', 100)->nullable(); // e.g., 'school', 'department', 'classroom'
            $table->unsignedBigInteger('location_id')->nullable();

            // Verification status
            $table->boolean('is_verified')->default(true);
            $table->string('verification_note', 255)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['attendance_id', 'scan_type']);
            $table->index('scanned_at');
            $table->index(['location_type', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_scans');
    }
};

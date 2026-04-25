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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('employee_code', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('birth_place')->nullable();
            $table->text('current_address')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->string('job_title', 100)->nullable();
            $table->enum('employee_type', ['full_time', 'part_time', 'contract', 'intern'])->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('probation_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->string('certificate')->nullable();
            $table->string('certificate_image')->nullable();
            $table->string('certificate_code')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('employee_qr_code')->nullable();
            $table->string('employee_barcode')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'employee_type']);
            $table->index('institution_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

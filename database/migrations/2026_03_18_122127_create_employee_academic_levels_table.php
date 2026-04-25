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
        Schema::create('employee_academic_levels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('level', [
                'high_school',
                'vocational',
                'associate',
                'bachelor',
                'master',
                'doctorate',
                'other'
            ]);
            $table->string('institution', 255);
            $table->string('field_of_study', 255)->nullable();
            $table->string('degree', 255)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('certificate', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_academic_levels');
    }
};

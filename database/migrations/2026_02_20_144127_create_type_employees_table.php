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
        Schema::create('employees_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('status');
        });

        // Add type_employee_id to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('type_employee_id')->nullable()->after('position_id');
            $table->index('type_employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['type_employee_id']);
            $table->dropColumn('type_employee_id');
        });

        Schema::dropIfExists('type_employees');
    }
};

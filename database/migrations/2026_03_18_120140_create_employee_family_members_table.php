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
        Schema::create('employee_family_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('relationship', ['spouse', 'child', 'father', 'mother', 'sibling'])->comment('Relationship to employee');
            $table->string('name', 100);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable()->comment('Can be calculated or stored');
            $table->string('occupation', 100)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_dependent')->default(false)->comment('For tax/benefits purposes');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'relationship']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_family_members');
    }
};

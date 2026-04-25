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
        Schema::table('employees_types', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('uuid')->constrained('schools')->onDelete('cascade');
            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees_types', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'status']);
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

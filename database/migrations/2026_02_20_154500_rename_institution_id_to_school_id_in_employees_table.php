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
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'institution_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('institution_id', 'school_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'school_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('school_id', 'institution_id');
            });
        }
    }
};

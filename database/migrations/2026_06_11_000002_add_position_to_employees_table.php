<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a free-text `position` field (e.g. "Head of Department"). The legacy
     * `position_id` integer column was never wired to a positions table.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('position', 100)->nullable()->after('job_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};

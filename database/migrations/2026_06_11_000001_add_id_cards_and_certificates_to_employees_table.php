<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds JSON array columns so an employee can hold multiple ID cards and
     * multiple certificates (the legacy single id_card_* / certificate_* columns
     * are kept for backward compatibility).
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->json('id_cards')->nullable()->after('id_card_expiry_date');
            $table->json('certificates')->nullable()->after('certificate_code');
        });
    }

    /**
     * Reverse the migrations.                  
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['id_cards', 'certificates']);
        });
    }
};

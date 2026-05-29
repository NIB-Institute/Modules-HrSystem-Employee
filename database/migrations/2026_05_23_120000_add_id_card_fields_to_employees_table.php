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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('id_card_number', 50)->nullable()->after('ethnicity');
            $table->string('id_card_front_url')->nullable()->after('id_card_number');
            $table->string('id_card_back_url')->nullable()->after('id_card_front_url');
            $table->date('id_card_issued_date')->nullable()->after('id_card_back_url');
            $table->date('id_card_expiry_date')->nullable()->after('id_card_issued_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'id_card_number',
                'id_card_front_url',
                'id_card_back_url',
                'id_card_issued_date',
                'id_card_expiry_date',
            ]);
        });
    }
};

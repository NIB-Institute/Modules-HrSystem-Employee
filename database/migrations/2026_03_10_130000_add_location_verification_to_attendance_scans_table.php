<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds location verification fields for industry-standard geofence checking.
     */
    public function up(): void
    {
        Schema::table('attendance_scans', function (Blueprint $table) {
            // Reference to the Location model (the designated scan location)
            $table->foreignId('designated_location_id')
                ->nullable()
                ->after('location_id')
                ->constrained('employee_locations')
                ->nullOnDelete();

            // Geofence verification results
            $table->boolean('within_geofence')->nullable()->after('is_verified');
            $table->decimal('distance_from_location', 10, 2)->nullable()->after('within_geofence'); // meters

            // Verification details
            $table->enum('verification_status', ['verified', 'outside_geofence', 'no_location', 'location_disabled'])
                ->default('verified')
                ->after('distance_from_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_scans', function (Blueprint $table) {
            $table->dropForeign(['designated_location_id']);
            $table->dropColumn([
                'designated_location_id',
                'within_geofence',
                'distance_from_location',
                'verification_status',
            ]);
        });
    }
};

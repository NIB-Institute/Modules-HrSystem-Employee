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
        Schema::table('employee_locations', function (Blueprint $table) {
            // Geofence type: 'circle' (default), 'polygon', 'dynamic'
            $table->enum('geofence_type', ['circle', 'polygon', 'dynamic'])
                ->default('circle')
                ->after('geofence_radius');

            // Polygon coordinates stored as JSON array of [lat, lng] pairs
            // Format: [[lat1, lng1], [lat2, lng2], [lat3, lng3], ...]
            // Minimum 3 points for a valid polygon
            $table->json('polygon_coordinates')->nullable()->after('geofence_type');

            // Dynamic geofence settings
            $table->unsignedBigInteger('reference_employee_id')->nullable()->after('polygon_coordinates');
            $table->unsignedInteger('dynamic_radius')->nullable()->after('reference_employee_id');

            // Current reference location (for dynamic type)
            $table->decimal('reference_latitude', 10, 8)->nullable()->after('dynamic_radius');
            $table->decimal('reference_longitude', 11, 8)->nullable()->after('reference_latitude');
            $table->timestamp('reference_location_updated_at')->nullable()->after('reference_longitude');

            // Foreign key to employees table
            $table->foreign('reference_employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();

            // Index for efficient queries
            $table->index('geofence_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_locations', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['reference_employee_id']);

            // Drop index
            $table->dropIndex(['geofence_type']);

            // Drop columns
            $table->dropColumn([
                'geofence_type',
                'polygon_coordinates',
                'reference_employee_id',
                'dynamic_radius',
                'reference_latitude',
                'reference_longitude',
                'reference_location_updated_at',
            ]);
        });
    }
};

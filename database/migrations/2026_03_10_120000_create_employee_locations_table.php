<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Industry-standard Location model for geofence-based attendance verification.
     * Supports multiple location types (office, branch, site, etc.)
     */
    public function up(): void
    {
        Schema::create('employee_locations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic info
            $table->string('name');
            $table->string('code', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['office', 'branch', 'site', 'remote', 'other'])->default('office');

            // Address
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            // GPS coordinates (center point)
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Geofence settings
            $table->unsignedInteger('geofence_radius')->default(100); // meters
            $table->boolean('enforce_geofence')->default(true);

            // Timezone for this location
            $table->string('timezone', 50)->default('UTC');

            // Operating hours (JSON: {"monday": {"start": "08:00", "end": "17:00"}, ...})
            $table->json('operating_hours')->nullable();

            // Polymorphic relationship (link to department, school, outlet, etc.)
            $table->string('locationable_type', 100)->nullable();
            $table->unsignedBigInteger('locationable_id')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['latitude', 'longitude']);
            $table->index(['locationable_type', 'locationable_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_locations');
    }
};

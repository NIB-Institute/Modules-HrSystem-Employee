<?php

namespace Modules\Employee\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\School\Models\School;

class Location extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_locations';

    /**
     * Location types.
     */
    public const TYPE_OFFICE = 'office';
    public const TYPE_BRANCH = 'branch';
    public const TYPE_SITE = 'site';
    public const TYPE_REMOTE = 'remote';
    public const TYPE_OTHER = 'other';

    /**
     * Geofence types.
     */
    public const GEOFENCE_CIRCLE = 'circle';
    public const GEOFENCE_POLYGON = 'polygon';
    public const GEOFENCE_DYNAMIC = 'dynamic';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'school_id',
        'name',
        'code',
        'description',
        'type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'geofence_radius',
        'geofence_type',
        'polygon_coordinates',
        'reference_employee_id',
        'dynamic_radius',
        'reference_latitude',
        'reference_longitude',
        'reference_location_updated_at',
        'enforce_geofence',
        'timezone',
        'operating_hours',
        'locationable_type',
        'locationable_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius' => 'integer',
        'polygon_coordinates' => 'array',
        'dynamic_radius' => 'integer',
        'reference_latitude' => 'decimal:8',
        'reference_longitude' => 'decimal:8',
        'reference_location_updated_at' => 'datetime',
        'enforce_geofence' => 'boolean',
        'operating_hours' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the school that this location belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the parent locationable model (department, school, etc.).
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the reference employee for dynamic geofence.
     */
    public function referenceEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reference_employee_id');
    }

    /**
     * Get available geofence types.
     */
    public static function getGeofenceTypes(): array
    {
        return [
            self::GEOFENCE_CIRCLE => 'Circle (Radius)',
            self::GEOFENCE_POLYGON => 'Polygon (Custom Shape)',
            self::GEOFENCE_DYNAMIC => 'Dynamic (Moving)',
        ];
    }

    /**
     * Get available location types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_OFFICE => 'Office',
            self::TYPE_BRANCH => 'Branch',
            self::TYPE_SITE => 'Site',
            self::TYPE_REMOTE => 'Remote',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Check if a GPS coordinate is within this location's geofence.
     *
     * Supports circle, polygon, and dynamic geofence types.
     *
     * @param float|null $lat Scan latitude
     * @param float|null $lng Scan longitude
     * @return array
     */
    public function verifyLocation(?float $lat, ?float $lng): array
    {
        // If no scan coordinates provided
        if ($lat === null || $lng === null) {
            return [
                'verified' => !$this->enforce_geofence,
                'within_geofence' => false,
                'distance_meters' => null,
                'geofence_radius' => $this->geofence_radius,
                'geofence_type' => $this->geofence_type ?? self::GEOFENCE_CIRCLE,
                'message' => $this->enforce_geofence
                    ? 'Location required for attendance'
                    : 'Location not provided (optional)',
            ];
        }

        // Verify based on geofence type
        return match ($this->geofence_type) {
            self::GEOFENCE_POLYGON => $this->verifyPolygonGeofence($lat, $lng),
            self::GEOFENCE_DYNAMIC => $this->verifyDynamicGeofence($lat, $lng),
            default => $this->verifyCircularGeofence($lat, $lng),
        };
    }

    /**
     * Verify location against circular geofence.
     */
    protected function verifyCircularGeofence(float $lat, float $lng): array
    {
        $distance = $this->calculateHaversineDistance(
            $this->latitude,
            $this->longitude,
            $lat,
            $lng
        );

        $withinGeofence = $distance <= $this->geofence_radius;

        return [
            'verified' => $withinGeofence || !$this->enforce_geofence,
            'within_geofence' => $withinGeofence,
            'distance_meters' => round($distance, 2),
            'geofence_radius' => $this->geofence_radius,
            'geofence_type' => self::GEOFENCE_CIRCLE,
            'message' => $withinGeofence
                ? 'Location verified'
                : ($this->enforce_geofence
                    ? sprintf('Outside allowed area (%.0fm away, max %dm)', $distance, $this->geofence_radius)
                    : sprintf('Outside geofence but allowed (%.0fm away)', $distance)),
        ];
    }

    /**
     * Verify location against polygon geofence using Ray Casting algorithm.
     */
    protected function verifyPolygonGeofence(float $lat, float $lng): array
    {
        if (empty($this->polygon_coordinates)) {
            return [
                'verified' => !$this->enforce_geofence,
                'within_geofence' => false,
                'distance_meters' => null,
                'geofence_type' => self::GEOFENCE_POLYGON,
                'message' => 'No polygon defined',
            ];
        }

        $isInside = $this->isPointInPolygon($lat, $lng, $this->polygon_coordinates);

        return [
            'verified' => $isInside || !$this->enforce_geofence,
            'within_geofence' => $isInside,
            'distance_meters' => null,
            'geofence_type' => self::GEOFENCE_POLYGON,
            'message' => $isInside
                ? 'Location verified (within polygon)'
                : ($this->enforce_geofence ? 'Outside allowed polygon area' : 'Outside polygon but allowed'),
        ];
    }

    /**
     * Verify location against dynamic geofence (moving reference point).
     */
    protected function verifyDynamicGeofence(float $lat, float $lng): array
    {
        // Check if reference location is available
        if (!$this->reference_latitude || !$this->reference_longitude) {
            return [
                'verified' => !$this->enforce_geofence,
                'within_geofence' => false,
                'distance_meters' => null,
                'geofence_type' => self::GEOFENCE_DYNAMIC,
                'message' => 'Reference location not available',
            ];
        }

        // Check if reference location is recent (< 5 minutes)
        $referenceAge = $this->reference_location_updated_at?->diffInMinutes(now());
        if ($referenceAge > 5) {
            return [
                'verified' => !$this->enforce_geofence,
                'within_geofence' => false,
                'distance_meters' => null,
                'geofence_type' => self::GEOFENCE_DYNAMIC,
                'message' => 'Reference location is stale (>5 min old)',
            ];
        }

        // Calculate distance from reference point
        $distance = $this->calculateHaversineDistance(
            $this->reference_latitude,
            $this->reference_longitude,
            $lat,
            $lng
        );

        $radius = $this->dynamic_radius ?? 100;
        $withinGeofence = $distance <= $radius;

        return [
            'verified' => $withinGeofence || !$this->enforce_geofence,
            'within_geofence' => $withinGeofence,
            'distance_meters' => round($distance, 2),
            'geofence_radius' => $radius,
            'geofence_type' => self::GEOFENCE_DYNAMIC,
            'reference_employee_id' => $this->reference_employee_id,
            'message' => $withinGeofence
                ? 'Location verified (near supervisor)'
                : sprintf('%.0fm from supervisor (max %dm)', $distance, $radius),
        ];
    }

    /**
     * Check if a point is inside a polygon using Ray Casting algorithm.
     *
     * @param float $lat Point latitude
     * @param float $lng Point longitude
     * @param array $polygon Array of [lat, lng] coordinates
     * @return bool
     */
    protected function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $n = count($polygon);
        if ($n < 3) {
            return false;
        }

        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0]; // lat
            $yi = $polygon[$i][1]; // lng
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            // Ray casting
            if ((($yi > $lng) !== ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Calculate polygon area in square meters (Shoelace formula).
     */
    public function calculatePolygonArea(): ?float
    {
        if ($this->geofence_type !== self::GEOFENCE_POLYGON || empty($this->polygon_coordinates)) {
            return null;
        }

        $polygon = $this->polygon_coordinates;
        $n = count($polygon);
        if ($n < 3) {
            return 0;
        }

        // Convert to approximate meters
        $avgLat = array_sum(array_column($polygon, 0)) / $n;
        $latScale = 111000; // meters per degree latitude
        $lngScale = 111000 * cos(deg2rad($avgLat));

        $area = 0;
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $x1 = $polygon[$i][1] * $lngScale;
            $y1 = $polygon[$i][0] * $latScale;
            $x2 = $polygon[$j][1] * $lngScale;
            $y2 = $polygon[$j][0] * $latScale;
            $area += ($x1 * $y2) - ($x2 * $y1);
        }

        return abs($area / 2);
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula.
     *
     * The Haversine formula determines the great-circle distance between two points
     * on a sphere given their longitudes and latitudes.
     *
     * @param float $lat1 First point latitude
     * @param float $lng1 First point longitude
     * @param float $lat2 Second point latitude
     * @param float $lng2 Second point longitude
     * @return float Distance in meters
     */
    public function calculateHaversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        // Haversine formula
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if currently within operating hours.
     */
    public function isWithinOperatingHours(): bool
    {
        if (!$this->operating_hours) {
            return true; // No restrictions
        }

        $now = now()->setTimezone($this->timezone);
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.

        if (!isset($this->operating_hours[$dayOfWeek])) {
            return false; // Closed on this day
        }

        $hours = $this->operating_hours[$dayOfWeek];
        $currentTime = $now->format('H:i');

        return $currentTime >= $hours['start'] && $currentTime <= $hours['end'];
    }

    /**
     * Get full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get Google Maps URL.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return sprintf(
            'https://www.google.com/maps?q=%s,%s',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * Get Google Maps embed URL for iframe.
     */
    public function getGoogleMapsEmbedUrlAttribute(): string
    {
        return sprintf(
            'https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=%s,%s&zoom=17',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to find locations near a GPS coordinate.
     */
    public function scopeNearby($query, float $lat, float $lng, int $radiusMeters = 1000)
    {
        // Approximate degree to meters conversion (at equator: 1 degree ≈ 111km)
        $latDelta = $radiusMeters / 111000;
        $lngDelta = $radiusMeters / (111000 * cos(deg2rad($lat)));

        return $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

<?php

namespace Modules\Employee\Models;

use Modules\Employee\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Modules\School\Models\School;
use Modules\School\Models\Department;
use Modules\School\Models\Classroom;

class AttendanceScan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'attendance_scans';

    /**
     * Scan types.
     */
    public const TYPE_CHECK_IN = 'check_in';
    public const TYPE_CHECK_OUT = 'check_out';

    /**
     * Scan methods.
     */
    public const METHOD_QR_SCAN = 'qr_scan';
    public const METHOD_MANUAL = 'manual';
    public const METHOD_BIOMETRIC = 'biometric';
    public const METHOD_FACE_RECOGNITION = 'face_recognition';

    /**
     * Verification statuses.
     */
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_OUTSIDE_GEOFENCE = 'outside_geofence';
    public const STATUS_NO_LOCATION = 'no_location';
    public const STATUS_LOCATION_DISABLED = 'location_disabled';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'attendance_id',
        'scan_type',
        'scanned_at',
        'timezone',
        'latitude',
        'longitude',
        'accuracy',
        'address',
        'scan_method',
        'device_info',
        'ip_address',
        'location_type',
        'location_id',
        'designated_location_id',
        'is_verified',
        'within_geofence',
        'distance_from_location',
        'verification_status',
        'verification_note',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scanned_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'distance_from_location' => 'decimal:2',
        'device_info' => 'array',
        'is_verified' => 'boolean',
        'within_geofence' => 'boolean',
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
     * Get the attendance record.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the location (polymorphic relationship - department, classroom, etc.).
     */
    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the designated Location for this scan.
     */
    public function designatedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'designated_location_id');
    }

    /**
     * Get available scan types.
     */
    public static function getScanTypes(): array
    {
        return [
            self::TYPE_CHECK_IN => 'Check In',
            self::TYPE_CHECK_OUT => 'Check Out',
        ];
    }

    /**
     * Get available scan methods.
     */
    public static function getScanMethods(): array
    {
        return [
            self::METHOD_QR_SCAN => 'QR Scan',
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_BIOMETRIC => 'Biometric',
            self::METHOD_FACE_RECOGNITION => 'Face Recognition',
        ];
    }

    /**
     * Scope for check-in scans.
     */
    public function scopeCheckIn($query)
    {
        return $query->where('scan_type', self::TYPE_CHECK_IN);
    }

    /**
     * Scope for check-out scans.
     */
    public function scopeCheckOut($query)
    {
        return $query->where('scan_type', self::TYPE_CHECK_OUT);
    }

    /**
     * Scope for verified scans.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for scans within geofence.
     */
    public function scopeWithinGeofence($query)
    {
        return $query->where('within_geofence', true);
    }

    /**
     * Scope for scans outside geofence.
     */
    public function scopeOutsideGeofence($query)
    {
        return $query->where('within_geofence', false);
    }

    /**
     * Get verification statuses.
     */
    public static function getVerificationStatuses(): array
    {
        return [
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_OUTSIDE_GEOFENCE => 'Outside Geofence',
            self::STATUS_NO_LOCATION => 'No Location',
            self::STATUS_LOCATION_DISABLED => 'Location Disabled',
        ];
    }

    /**
     * Check if scan is verified and within geofence.
     */
    public function isFullyVerified(): bool
    {
        return $this->is_verified && $this->within_geofence === true;
    }

    /**
     * Get verification badge variant for UI.
     */
    public function getVerificationBadgeVariant(): string
    {
        return match ($this->verification_status) {
            self::STATUS_VERIFIED => 'success',
            self::STATUS_OUTSIDE_GEOFENCE => 'warning',
            self::STATUS_NO_LOCATION => 'secondary',
            self::STATUS_LOCATION_DISABLED => 'outline',
            default => 'secondary',
        };
    }

    /**
     * Check if this is a check-in scan.
     */
    public function isCheckIn(): bool
    {
        return $this->scan_type === self::TYPE_CHECK_IN;
    }

    /**
     * Check if this is a check-out scan.
     */
    public function isCheckOut(): bool
    {
        return $this->scan_type === self::TYPE_CHECK_OUT;
    }

    /**
     * Get formatted coordinates.
     */
    public function getFormattedCoordinates(): ?string
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return sprintf('%.6f, %.6f', $this->latitude, $this->longitude);
    }

    /**
     * Get Google Maps URL for the scan location.
     */
    public function getGoogleMapsUrl(): ?string
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return sprintf(
            'https://www.google.com/maps?q=%s,%s',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * Get the scanned_at in the scan's timezone.
     */
    public function getLocalScannedAt(): ?\Carbon\Carbon
    {
        if (!$this->scanned_at) {
            return null;
        }

        if ($this->timezone) {
            return $this->scanned_at->setTimezone($this->timezone);
        }

        return $this->scanned_at;
    }

    /**
     * Get device type from device_info.
     */
    public function getDeviceType(): ?string
    {
        return $this->device_info['device_type'] ?? null;
    }

    /**
     * Get browser from device_info.
     */
    public function getBrowser(): ?string
    {
        return $this->device_info['browser'] ?? null;
    }

    /**
     * Get OS from device_info.
     */
    public function getOs(): ?string
    {
        return $this->device_info['os'] ?? null;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

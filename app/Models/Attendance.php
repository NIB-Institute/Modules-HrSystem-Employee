<?php

namespace Modules\Employee\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\School\Models\School;
use Modules\School\Models\Department;
use Modules\School\Models\Classroom;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_attendances';

    /**
     * Attendance statuses.
     */
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EARLY_LEAVE = 'early_leave';
    public const STATUS_HALF_DAY = 'half_day';
    public const STATUS_ON_LEAVE = 'on_leave';

    /**
     * Check-in methods.
     */
    public const METHOD_QR_SCAN = 'qr_scan';
    public const METHOD_MANUAL = 'manual';
    public const METHOD_BIOMETRIC = 'biometric';
    public const METHOD_FACE_RECOGNITION = 'face_recognition';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'school_id',
        'department_id',
        'classroom_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'check_in_method',
        'check_out_method',
        'check_in_location',
        'check_out_location',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'work_hours',
        'overtime_hours',
        'notes',
        'device_info',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'work_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
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

        static::saving(function ($model) {
            // Calculate work hours if both check-in and check-out are set
            if ($model->check_in_time && $model->check_out_time) {
                $checkIn = \Carbon\Carbon::parse($model->check_in_time);
                $checkOut = \Carbon\Carbon::parse($model->check_out_time);
                $model->work_hours = $checkOut->diffInMinutes($checkIn) / 60;
            }
        });
    }

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the school associated with the attendance.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the department associated with the attendance.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the classroom associated with the attendance.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get all scans for this attendance.
     */
    public function scans(): HasMany
    {
        return $this->hasMany(AttendanceScan::class)->orderBy('scanned_at');
    }

    /**
     * Get the check-in scan.
     */
    public function checkInScan(): HasOne
    {
        return $this->hasOne(AttendanceScan::class)
            ->where('scan_type', AttendanceScan::TYPE_CHECK_IN)
            ->latest('scanned_at');
    }

    /**
     * Get the check-out scan.
     */
    public function checkOutScan(): HasOne
    {
        return $this->hasOne(AttendanceScan::class)
            ->where('scan_type', AttendanceScan::TYPE_CHECK_OUT)
            ->latest('scanned_at');
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LATE => 'Late',
            self::STATUS_EARLY_LEAVE => 'Early Leave',
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_ON_LEAVE => 'On Leave',
        ];
    }

    /**
     * Get available check-in methods.
     */
    public static function getMethods(): array
    {
        return [
            self::METHOD_QR_SCAN => 'QR Scan',
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_BIOMETRIC => 'Biometric',
            self::METHOD_FACE_RECOGNITION => 'Face Recognition',
        ];
    }

    /**
     * Scope for today's attendance.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    /**
     * Scope for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope for a specific employee.
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Check if employee has checked in.
     */
    public function hasCheckedIn(): bool
    {
        return !is_null($this->check_in_time);
    }

    /**
     * Check if employee has checked out.
     */
    public function hasCheckedOut(): bool
    {
        return !is_null($this->check_out_time);
    }

    /**
     * Get formatted work hours.
     */
    public function getFormattedWorkHours(): string
    {
        if (!$this->work_hours) {
            return '-';
        }

        $hours = floor($this->work_hours);
        $minutes = round(($this->work_hours - $hours) * 60);

        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

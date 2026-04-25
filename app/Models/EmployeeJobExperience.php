<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Employee\Enums\EmploymentTypeEnum;

class EmployeeJobExperience extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_job_experiences';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'company',
        'position',
        'employment_type',
        'province',
        'city',
        'start_date',
        'end_date',
        'is_current',
        'responsibilities',
        'achievements',
        'reason_for_leaving',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'employment_type' => EmploymentTypeEnum::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
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
     * Get the employee that owns the job experience.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate duration in months.
     */
    public function getDurationInMonthsAttribute(): int
    {
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInMonths($endDate);
    }

    /**
     * Get formatted duration.
     */
    public function getDurationFormattedAttribute(): string
    {
        $months = $this->duration_in_months;
        $years = floor($months / 12);
        $remainingMonths = $months % 12;

        if ($years > 0 && $remainingMonths > 0) {
            return "{$years} year(s), {$remainingMonths} month(s)";
        } elseif ($years > 0) {
            return "{$years} year(s)";
        } else {
            return "{$remainingMonths} month(s)";
        }
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

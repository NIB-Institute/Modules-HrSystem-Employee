<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Employee\Enums\AcademicLevelEnum;

class EmployeeAcademicLevel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_academic_levels';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'level',
        'institution',
        'field_of_study',
        'degree',
        'start_date',
        'end_date',
        'gpa',
        'certificate',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'level' => AcademicLevelEnum::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'gpa' => 'decimal:2',
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
     * Get the employee that owns the academic level.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for a specific level.
     */
    public function scopeOfLevel($query, AcademicLevelEnum|string $level)
    {
        $value = $level instanceof AcademicLevelEnum ? $level->value : $level;
        return $query->where('level', $value);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

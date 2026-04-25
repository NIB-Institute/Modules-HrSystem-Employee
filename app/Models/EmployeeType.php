<?php

namespace Modules\Employee\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Employee\Database\Factories\EmployeeTypeFactory;
use Modules\School\Models\School;

class EmployeeType extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    /**
     * The table associated with the model.
     */
    protected $table = 'employees_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'school_id',
        'name',
        'time_start',
        'time_end',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => true,
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
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): EmployeeTypeFactory
    {
        return EmployeeTypeFactory::new();
    }

    /**
     * Get the school that owns this employee type.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the employees that belong to this type.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'type_employee_id');
    }

    /**
     * Scope for a specific school.
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

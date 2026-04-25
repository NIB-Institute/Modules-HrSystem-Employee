<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Employee\Enums\FamilyRelationshipEnum;

class EmployeeFamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_family_members';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'relationship',
        'name',
        'gender',
        'date_of_birth',
        'age',
        'occupation',
        'phone_number',
        'email',
        'address',
        'notes',
        'is_emergency_contact',
        'is_dependent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'relationship' => FamilyRelationshipEnum::class,
        'date_of_birth' => 'date',
        'age' => 'integer',
        'is_emergency_contact' => 'boolean',
        'is_dependent' => 'boolean',
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

            // Auto-calculate age from date_of_birth if provided
            if ($model->date_of_birth && empty($model->age)) {
                $model->age = $model->date_of_birth->age;
            }
        });

        static::updating(function ($model) {
            // Recalculate age from date_of_birth if provided
            if ($model->date_of_birth) {
                $model->age = $model->date_of_birth->age;
            }
        });
    }

    /**
     * Get the employee that this family member belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for a specific relationship type.
     */
    public function scopeOfRelationship($query, FamilyRelationshipEnum|string $relationship)
    {
        $value = $relationship instanceof FamilyRelationshipEnum ? $relationship->value : $relationship;
        return $query->where('relationship', $value);
    }

    /**
     * Scope for emergency contacts.
     */
    public function scopeEmergencyContacts($query)
    {
        return $query->where('is_emergency_contact', true);
    }

    /**
     * Scope for dependents.
     */
    public function scopeDependents($query)
    {
        return $query->where('is_dependent', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

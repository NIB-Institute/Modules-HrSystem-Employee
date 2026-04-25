<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Employee\Enums\LanguageProficiencyEnum;

class EmployeeForeignLanguage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_foreign_languages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'language',
        'proficiency',
        'certificate',
        'certificate_score',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'proficiency' => LanguageProficiencyEnum::class,
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
     * Get the employee that owns the language.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for a specific proficiency.
     */
    public function scopeOfProficiency($query, LanguageProficiencyEnum|string $proficiency)
    {
        $value = $proficiency instanceof LanguageProficiencyEnum ? $proficiency->value : $proficiency;
        return $query->where('proficiency', $value);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

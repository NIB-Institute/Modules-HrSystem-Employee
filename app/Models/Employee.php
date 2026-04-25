<?php

namespace Modules\Employee\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\School\Models\School;
use Modules\School\Models\Department;
use Modules\School\Models\Course;
use Modules\Employee\Models\EmployeeType;
use Modules\Employee\Models\EmployeeFamilyMember;
use Modules\Employee\Models\EmployeeAcademicLevel;
use Modules\Employee\Models\EmployeeForeignLanguage;
use Modules\Employee\Models\EmployeeJobExperience;
use Modules\Employee\Enums\MaritalStatusEnum;
use Modules\Employee\Enums\FamilyRelationshipEnum;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;
    // use HasSwitchDatabase;

    /**
     * Employee types.
     */
    public const TYPE_FULL_TIME = 'full_time';
    public const TYPE_PART_TIME = 'part_time';
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_INTERN = 'intern';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'gender',
        'marital_status',
        'date_of_birth',
        'birth_place',
        'ethnicity',
        'current_address',
        'school_id',
        'department_id',
        'position_id',
        'type_employee_id',
        'job_title',
        'employee_type',
        'anttendent_value',
        'salary',
        'hire_date',
        'probation_date',
        'probation_end_date',
        'certificate',
        'certificate_image',
        'certificate_images',
        'certificate_code',
        'avatar_url',
        'employee_qr_code',
        'employee_barcode',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'probation_date' => 'date',
        'probation_end_date' => 'date',
        'salary' => 'decimal:2',
        'status' => 'boolean',
        'marital_status' => MaritalStatusEnum::class,
        'certificate_images' => 'array',
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
     * Get the employee's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . $this->last_name),
        );
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school that the employee belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the courses taught by the employee (as instructor).
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the family members of the employee.
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    /**
     * Get the spouse of the employee.
     */
    public function spouse(): HasMany
    {
        return $this->familyMembers()->where('relationship', FamilyRelationshipEnum::SPOUSE);
    }

    /**
     * Get the children of the employee.
     */
    public function children(): HasMany
    {
        return $this->familyMembers()->where('relationship', FamilyRelationshipEnum::CHILD);
    }

    /**
     * Get the father of the employee.
     */
    public function father(): HasMany
    {
        return $this->familyMembers()->where('relationship', FamilyRelationshipEnum::FATHER);
    }

    /**
     * Get the mother of the employee.
     */
    public function mother(): HasMany
    {
        return $this->familyMembers()->where('relationship', FamilyRelationshipEnum::MOTHER);
    }

    /**
     * Get the siblings of the employee.
     */
    public function siblings(): HasMany
    {
        return $this->familyMembers()->where('relationship', FamilyRelationshipEnum::SIBLING);
    }

    /**
     * Get the academic levels of the employee.
     */
    public function academicLevels(): HasMany
    {
        return $this->hasMany(EmployeeAcademicLevel::class);
    }

    /**
     * Get the foreign languages of the employee.
     */
    public function foreignLanguages(): HasMany
    {
        return $this->hasMany(EmployeeForeignLanguage::class);
    }

    /**
     * Get the job experiences of the employee.
     */
    public function jobExperiences(): HasMany
    {
        return $this->hasMany(EmployeeJobExperience::class);
    }

    /**
     * Get available employee types.
     */
    public static function getEmployeeTypes(): array
    {
        return [
            self::TYPE_FULL_TIME => 'Full Time',
            self::TYPE_PART_TIME => 'Part Time',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_INTERN => 'Intern',
        ];
    }

    /**
     * Scope for active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Check if employee is on probation.
     */
    public function isOnProbation(): bool
    {
        if (!$this->probation_end_date) {
            return false;
        }

        return now()->lessThan($this->probation_end_date);
    }

    /**
     * Get the employee type that the employee belongs to.
     */
    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'type_employee_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

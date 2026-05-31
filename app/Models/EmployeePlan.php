<?php

namespace Modules\Employee\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\Enums\EmployeePlanEnum;

class EmployeePlan extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'employee_plan';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'priority',
        'location',
        'telegram_group_chat_id',
        'telegram_group_name',
        'schedule_mode',
        'is_recurring',
        'recurrence_type',
        'status',
        'valid_for_months',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
        'valid_for_months' => 'integer',
    ];

    public const PRIORITIES = EmployeePlanEnum::PRIORITIES;
    public const SCHEDULE_MODES = EmployeePlanEnum::SCHEDULE_MODES;
    public const RECURRENCE_TYPES = EmployeePlanEnum::RECURRENCE_TYPES;
    public const STATUSES = EmployeePlanEnum::STATUSES;

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeePlanAssignment::class, 'employee_plan_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'employee_plan_assignments',
            'employee_plan_id',
            'employee_id',
        )->whereNull('employee_plan_assignments.deleted_at')
            ->withPivot([
                'uuid',
                'employee_availability_id',
                'status',
                'assigned_at',
                'started_at',
                'completed_at',
                'expires_at',
                'notes',
            ])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

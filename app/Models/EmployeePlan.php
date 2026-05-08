<?php

namespace Modules\Employee\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'employee_id',
        'employee_availability_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'priority',
        'location',
        'schedule_mode',
        'participants',
        'is_recurring',
        'recurrence_type',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'participants' => 'array',
        'is_recurring' => 'boolean',
    ];

    public const PRIORITIES = EmployeePlanEnum::PRIORITIES;
    public const SCHEDULE_MODES = EmployeePlanEnum::SCHEDULE_MODES;
    public const RECURRENCE_TYPES = EmployeePlanEnum::RECURRENCE_TYPES;
    public const STATUSES = EmployeePlanEnum::STATUSES;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeAvailability(): BelongsTo
    {
        return $this->belongsTo(EmployeeAvailability::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

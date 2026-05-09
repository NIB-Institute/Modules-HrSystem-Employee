<?php

namespace Modules\Employee\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\Enums\EmployeePlanAssignmentEnum;

class EmployeePlanAssignment extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'employee_plan_assignments';

    protected $fillable = [
        'uuid',
        'employee_plan_id',
        'employee_id',
        'employee_availability_id',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUSES = EmployeePlanAssignmentEnum::STATUSES;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(EmployeePlan::class, 'employee_plan_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(EmployeeAvailability::class, 'employee_availability_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

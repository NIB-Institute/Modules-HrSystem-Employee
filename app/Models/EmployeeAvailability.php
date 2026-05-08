<?php

namespace Modules\Employee\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\Enums\EmployeeAvailabilityEnum;

class EmployeeAvailability extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'employee_availability';

    protected $fillable = [
        'uuid',
        'employee_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const DAYS = EmployeeAvailabilityEnum::DAYS;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(EmployeePlan::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

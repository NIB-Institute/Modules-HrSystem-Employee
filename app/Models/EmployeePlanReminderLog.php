<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePlanReminderLog extends Model
{
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'employee_plan_reminder_logs';

    protected $fillable = [
        'employee_plan_id',
        'occurrence_date',
        'tier',
        'channel',
        'status',
        'telegram_message_id',
        'error',
        'recipient_count',
        'sent_at',
    ];

    protected $casts = [
        'occurrence_date' => 'date',
        'sent_at' => 'datetime',
        'recipient_count' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(EmployeePlan::class, 'employee_plan_id');
    }
}

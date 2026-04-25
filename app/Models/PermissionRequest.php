<?php

namespace Modules\Employee\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PermissionRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Permission request types.
     */
    public const TYPE_LEAVE = 'leave';
    public const TYPE_OVERTIME = 'overtime';
    public const TYPE_REMOTE = 'remote';
    public const TYPE_EARLY_LEAVE = 'early_leave';
    public const TYPE_LATE_ARRIVAL = 'late_arrival';
    public const TYPE_OTHER = 'other';

    /**
     * Status types.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * The table associated with the model.
     */
    protected $table = 'permission_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'employee_id',
        'type',
        'reason',
        'from_date',
        'to_date',
        'request_date',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'rejected_status',
        'rejected_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'request_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'rejected_status' => 'boolean',
    ];

    /**
     * Default attributes.
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
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
            if (empty($model->request_date)) {
                $model->request_date = now();
            }
        });
    }

    /**
     * Get available permission types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_LEAVE => 'Leave Request',
            self::TYPE_OVERTIME => 'Overtime',
            self::TYPE_REMOTE => 'Work From Home',
            self::TYPE_EARLY_LEAVE => 'Early Leave',
            self::TYPE_LATE_ARRIVAL => 'Late Arrival',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get type descriptions.
     */
    public static function getTypeDescriptions(): array
    {
        return [
            self::TYPE_LEAVE => 'Request time off or vacation leave',
            self::TYPE_OVERTIME => 'Request to work extra hours',
            self::TYPE_REMOTE => 'Request to work remotely',
            self::TYPE_EARLY_LEAVE => 'Request to leave early',
            self::TYPE_LATE_ARRIVAL => 'Request permission for late arrival',
            self::TYPE_OTHER => 'Other permission requests',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get the employee that owns the request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Calculate total days requested.
     */
    public function getTotalDays(): int
    {
        if (!$this->from_date || !$this->to_date) {
            return 0;
        }
        return $this->from_date->diffInDays($this->to_date) + 1;
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the request.
     */
    public function approve(?int $reviewerId = null, ?string $note = null, bool $rejectedStatus = false, ?string $rejectedReason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $note,
            'rejected_status' => $rejectedStatus,
            'rejected_reason' => $rejectedReason,
        ]);
    }

    /**
     * Reject the request.
     */
    public function reject(?int $reviewerId = null, ?string $note = null, bool $rejectedStatus = false, ?string $rejectedReason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $note,
            'rejected_status' => $rejectedStatus,
            'rejected_reason' => $rejectedReason,
        ]);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

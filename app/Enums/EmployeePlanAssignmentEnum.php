<?php

namespace Modules\Employee\Enums;

/**
 * Single source of truth for EmployeePlanAssignment status values
 * (per-employee progress on a plan).
 */
final class EmployeePlanAssignmentEnum
{
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DROPPED = 'dropped';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_DROPPED,
        self::STATUS_NO_SHOW,
        self::STATUS_EXPIRED,
    ];

    public static function statusLabel(string $value): string
    {
        return match ($value) {
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_DROPPED => 'Dropped',
            self::STATUS_NO_SHOW => 'No Show',
            self::STATUS_EXPIRED => 'Expired',
            default => $value,
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function statusOptions(): array
    {
        return array_map(
            static fn (string $v): array => ['value' => $v, 'label' => self::statusLabel($v)],
            self::STATUSES,
        );
    }
}

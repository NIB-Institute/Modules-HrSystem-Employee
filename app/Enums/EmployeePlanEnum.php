<?php

namespace Modules\Employee\Enums;

/**
 * Single source of truth for the EmployeePlan domain values
 * (priority, schedule mode, recurrence type, status).
 *
 * Used by the model, FormRequests, Actions, and any other code
 * that needs to validate or display these values.
 */
final class EmployeePlanEnum
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const SCHEDULE_MODE_SINGLE = 'single';
    public const SCHEDULE_MODE_RECURRING = 'recurring';

    public const RECURRENCE_DAILY = 'daily';
    public const RECURRENCE_WEEKLY = 'weekly';
    public const RECURRENCE_MONTHLY = 'monthly';
    public const RECURRENCE_YEARLY = 'yearly';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    public const SCHEDULE_MODES = [
        self::SCHEDULE_MODE_SINGLE,
        self::SCHEDULE_MODE_RECURRING,
    ];

    public const RECURRENCE_TYPES = [
        self::RECURRENCE_DAILY,
        self::RECURRENCE_WEEKLY,
        self::RECURRENCE_MONTHLY,
        self::RECURRENCE_YEARLY,
    ];

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public static function priorityLabel(string $value): string
    {
        return match ($value) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            default => $value,
        };
    }

    public static function scheduleModeLabel(string $value): string
    {
        return match ($value) {
            self::SCHEDULE_MODE_SINGLE => 'Single',
            self::SCHEDULE_MODE_RECURRING => 'Recurring',
            default => $value,
        };
    }

    public static function recurrenceTypeLabel(string $value): string
    {
        return match ($value) {
            self::RECURRENCE_DAILY => 'Daily',
            self::RECURRENCE_WEEKLY => 'Weekly',
            self::RECURRENCE_MONTHLY => 'Monthly',
            self::RECURRENCE_YEARLY => 'Yearly',
            default => $value,
        };
    }

    public static function statusLabel(string $value): string
    {
        return match ($value) {
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => $value,
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function priorityOptions(): array
    {
        return array_map(
            static fn (string $v): array => ['value' => $v, 'label' => self::priorityLabel($v)],
            self::PRIORITIES,
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function scheduleModeOptions(): array
    {
        return array_map(
            static fn (string $v): array => ['value' => $v, 'label' => self::scheduleModeLabel($v)],
            self::SCHEDULE_MODES,
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function recurrenceTypeOptions(): array
    {
        return array_map(
            static fn (string $v): array => ['value' => $v, 'label' => self::recurrenceTypeLabel($v)],
            self::RECURRENCE_TYPES,
        );
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

<?php

namespace Modules\Employee\Enums;

/**
 * Single source of truth for the EmployeeAvailability domain values.
 *
 * Models a recurring weekly availability template — one row per shift
 * per day-of-week per employee.
 */
final class EmployeeAvailabilityEnum
{
    public const DAY_MONDAY = 'monday';
    public const DAY_TUESDAY = 'tuesday';
    public const DAY_WEDNESDAY = 'wednesday';
    public const DAY_THURSDAY = 'thursday';
    public const DAY_FRIDAY = 'friday';
    public const DAY_SATURDAY = 'saturday';
    public const DAY_SUNDAY = 'sunday';

    public const DAYS = [
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
        self::DAY_SATURDAY,
        self::DAY_SUNDAY,
    ];

    public const WEEKDAYS = [
        self::DAY_MONDAY,
        self::DAY_TUESDAY,
        self::DAY_WEDNESDAY,
        self::DAY_THURSDAY,
        self::DAY_FRIDAY,
    ];

    public const WEEKEND = [
        self::DAY_SATURDAY,
        self::DAY_SUNDAY,
    ];

    public static function dayLabel(string $value): string
    {
        return match ($value) {
            self::DAY_MONDAY => 'Monday',
            self::DAY_TUESDAY => 'Tuesday',
            self::DAY_WEDNESDAY => 'Wednesday',
            self::DAY_THURSDAY => 'Thursday',
            self::DAY_FRIDAY => 'Friday',
            self::DAY_SATURDAY => 'Saturday',
            self::DAY_SUNDAY => 'Sunday',
            default => $value,
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function dayOptions(): array
    {
        return array_map(
            static fn (string $v): array => ['value' => $v, 'label' => self::dayLabel($v)],
            self::DAYS,
        );
    }

    /**
     * Numeric index (0=Mon … 6=Sun) for a day, useful for sorting.
     */
    public static function dayOrder(string $value): int
    {
        return array_search($value, self::DAYS, true);
    }
}

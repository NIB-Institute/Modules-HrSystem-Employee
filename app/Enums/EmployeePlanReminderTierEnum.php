<?php

namespace Modules\Employee\Enums;

enum EmployeePlanReminderTierEnum: string
{
    case ON_ASSIGNMENT = 'on_assignment';
    case UPCOMING_7D = '7d';
    case UPCOMING_3D = '3d';
    case UPCOMING_1D = '1d';
    case GROUP_3D = 'group_3d';
    case GROUP_1D = 'group_1d';

    /**
     * Tiers fired by the hourly scheduler (excludes on_assignment which is event-driven).
     *
     * @return array<int, self>
     */
    public static function scheduledTiers(): array
    {
        return [
            self::UPCOMING_7D,
            self::UPCOMING_3D,
            self::UPCOMING_1D,
            self::GROUP_3D,
            self::GROUP_1D,
        ];
    }

    public function isGroupTier(): bool
    {
        return in_array($this, [self::GROUP_3D, self::GROUP_1D], true);
    }

    /**
     * Days before the occurrence when this tier fires.
     */
    public function daysBefore(): int
    {
        return match ($this) {
            self::UPCOMING_7D => 7,
            self::UPCOMING_3D, self::GROUP_3D => 3,
            self::UPCOMING_1D, self::GROUP_1D => 1,
            self::ON_ASSIGNMENT => 0,
        };
    }

    /**
     * Start hour of the 1-hour fire window (school local time).
     */
    public function fireHour(): int
    {
        return match ($this) {
            self::UPCOMING_7D, self::UPCOMING_3D, self::GROUP_3D => 9,
            self::UPCOMING_1D, self::GROUP_1D => 18,
            self::ON_ASSIGNMENT => 0,
        };
    }
}

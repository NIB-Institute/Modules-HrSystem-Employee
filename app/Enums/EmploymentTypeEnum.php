<?php

namespace Modules\Employee\Enums;

enum EmploymentTypeEnum: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case FREELANCE = 'freelance';
    case INTERNSHIP = 'internship';

    /**
     * Get the display label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => 'Full Time',
            self::PART_TIME => 'Part Time',
            self::CONTRACT => 'Contract',
            self::FREELANCE => 'Freelance',
            self::INTERNSHIP => 'Internship',
        };
    }

    /**
     * Get all types as options array.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    /**
     * Get all type values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

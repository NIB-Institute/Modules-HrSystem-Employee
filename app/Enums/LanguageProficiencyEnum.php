<?php

namespace Modules\Employee\Enums;

enum LanguageProficiencyEnum: string
{
    case BEGINNER = 'beginner';
    case ELEMENTARY = 'elementary';
    case INTERMEDIATE = 'intermediate';
    case UPPER_INTERMEDIATE = 'upper_intermediate';
    case ADVANCED = 'advanced';
    case NATIVE = 'native';

    /**
     * Get the display label for the proficiency.
     */
    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'Beginner (A1)',
            self::ELEMENTARY => 'Elementary (A2)',
            self::INTERMEDIATE => 'Intermediate (B1)',
            self::UPPER_INTERMEDIATE => 'Upper Intermediate (B2)',
            self::ADVANCED => 'Advanced (C1/C2)',
            self::NATIVE => 'Native',
        };
    }

    /**
     * Get all proficiencies as options array.
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
     * Get all proficiency values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

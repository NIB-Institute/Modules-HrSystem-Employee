<?php

namespace Modules\Employee\Enums;

enum FamilyRelationshipEnum: string
{
    case SPOUSE = 'spouse';
    case CHILD = 'child';
    case FATHER = 'father';
    case MOTHER = 'mother';
    case SIBLING = 'sibling';

    /**
     * Get the display label for the relationship.
     */
    public function label(): string
    {
        return match ($this) {
            self::SPOUSE => 'Spouse',
            self::CHILD => 'Child',
            self::FATHER => 'Father',
            self::MOTHER => 'Mother',
            self::SIBLING => 'Sibling',
        };
    }

    /**
     * Get all relationships as options array.
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
     * Get all relationship values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if relationship allows multiple entries.
     */
    public function allowsMultiple(): bool
    {
        return match ($this) {
            self::CHILD, self::SIBLING => true,
            default => false,
        };
    }
}

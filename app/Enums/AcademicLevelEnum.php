<?php

namespace Modules\Employee\Enums;

enum AcademicLevelEnum: string
{
    case HIGH_SCHOOL = 'high_school';
    case VOCATIONAL = 'vocational';
    case ASSOCIATE = 'associate';
    case BACHELOR = 'bachelor';
    case MASTER = 'master';
    case DOCTORATE = 'doctorate';
    case OTHER = 'other';

    /**
     * Get the display label for the level.
     */
    public function label(): string
    {
        return match ($this) {
            self::HIGH_SCHOOL => 'High School',
            self::VOCATIONAL => 'Vocational',
            self::ASSOCIATE => 'Associate Degree',
            self::BACHELOR => 'Bachelor\'s Degree',
            self::MASTER => 'Master\'s Degree',
            self::DOCTORATE => 'Doctorate',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get all levels as options array.
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
     * Get all level values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

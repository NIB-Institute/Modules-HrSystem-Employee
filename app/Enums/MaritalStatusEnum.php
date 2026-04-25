<?php

namespace Modules\Employee\Enums;

enum MaritalStatusEnum: string
{
    case SINGLE = 'single';
    case MARRIED = 'married';

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Single',
            self::MARRIED => 'Married',
        };
    }

    /**
     * Get all statuses as options array.
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
     * Get all status values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

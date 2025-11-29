<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A backed enum with string values.
 *
 * Used for testing EnumWrapper with backed enum features.
 *
 * @api
 */
enum StringBackedEnumFixture: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Inactive = 'inactive';

    /**
     * Get a human-readable label for the status.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active Status',
            self::Pending => 'Pending Status',
            self::Inactive => 'Inactive Status',
        };
    }

    /**
     * Check if the status is active.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /**
     * Get all status values.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A backed enum with integer values.
 *
 * @api
 */
enum IntBackedEnumFixture: int
{
    case Low = 1;
    case Medium = 5;
    case High = 10;
    case Critical = 100;

    /**
     * Get the numeric priority value.
     */
    public function getPriority(): int
    {
        return $this->value;
    }

    /**
     * Check if priority is high or critical.
     */
    public function isUrgent(): bool
    {
        return $this->value >= self::High->value;
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

use InvalidArgumentException;
use RuntimeException;
use LogicException;

/**
 * Fixture for testing @throws tag resolution.
 *
 * @api
 */
class ThrowsTagFixture
{
    /**
     * Method that throws one exception.
     *
     * @throws InvalidArgumentException When argument is invalid.
     */
    public function throwsOne(): void
    {
        throw new InvalidArgumentException('Invalid');
    }

    /**
     * Method that throws multiple exceptions.
     *
     * @throws InvalidArgumentException When argument is invalid.
     * @throws RuntimeException When runtime error occurs.
     */
    public function throwsMultiple(): void
    {
        throw new InvalidArgumentException('Invalid');
    }

    /**
     * Method that throws with inheritance.
     *
     * @throws LogicException A logic exception.
     */
    public function throwsLogicException(): void
    {
        throw new LogicException('Logic error');
    }

    /**
     * Method that doesn't throw.
     */
    public function doesNotThrow(): void {}

    /**
     * Method with complex throws.
     *
     * @param int $type The type of exception to throw.
     *
     * @throws InvalidArgumentException When type is 1.
     * @throws RuntimeException When type is 2.
     * @throws LogicException When type is 3.
     */
    public function throwsBasedOnType(int $type): void
    {
        match ($type) {
            1 => throw new InvalidArgumentException('Type 1'),
            2 => throw new RuntimeException('Type 2'),
            3 => throw new LogicException('Type 3'),
            default => null,
        };
    }
}

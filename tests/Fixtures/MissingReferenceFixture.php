<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * Fixture for testing @throws tags that reference classes that should exist
 * in the configured namespace but don't (e.g., typos).
 *
 * @api
 */
class MissingReferenceFixture
{
    /**
     * Method that throws a non-existent exception from the Fixtures namespace.
     *
     * This simulates a typo in the @throws tag where the exception class
     * should exist in the Fixtures namespace but doesn't.
     *
     * @throws NonExistentFixtureException When something goes wrong.
     */
    public function methodWithMissingThrows(): void
    {
        throw new \RuntimeException('Error');
    }

    /**
     * Method that throws a non-existent exception with full namespace path.
     *
     * @throws AnotherMissingException When another error occurs.
     */
    public function methodWithFullNamespaceMissingThrows(): void
    {
        throw new \RuntimeException('Error');
    }
}

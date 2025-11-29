<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A secondary trait for testing multiple trait usage.
 *
 * @api
 */
trait SecondaryTraitFixture
{
    /**
     * A property from the secondary trait.
     */
    private bool $secondaryFlag = false;

    /**
     * Toggle the secondary flag.
     *
     * @return bool The new flag state.
     */
    public function toggleSecondaryFlag(): bool
    {
        $this->secondaryFlag = !$this->secondaryFlag;
        return $this->secondaryFlag;
    }
}

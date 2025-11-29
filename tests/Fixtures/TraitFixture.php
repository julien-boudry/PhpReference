<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A trait fixture for testing TraitWrapper.
 *
 * Contains various trait methods and properties.
 *
 * @api
 */
trait TraitFixture
{
    /**
     * A trait property.
     */
    protected string $traitProperty = 'trait_default';

    /**
     * A static trait property.
     */
    protected static int $staticTraitProperty = 0;

    /**
     * Get the trait property value.
     *
     * @return string The trait property value.
     */
    public function getTraitProperty(): string
    {
        return $this->traitProperty;
    }

    /**
     * Set the trait property value.
     *
     * @param string $value The value to set.
     */
    public function setTraitProperty(string $value): void
    {
        $this->traitProperty = $value;
    }

    /**
     * A protected trait method.
     */
    protected function protectedTraitMethod(): void {}

    /**
     * A static trait method.
     *
     * @return int The static counter value.
     */
    public static function getStaticTraitCounter(): int
    {
        return self::$staticTraitProperty++;
    }
}

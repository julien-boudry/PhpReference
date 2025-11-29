<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * Child class that extends the base class.
 *
 * Used for testing inheritance in reflection wrappers.
 *
 * @api
 */
class ChildClassFixture extends BaseClassFixture implements InterfaceFixture, SecondaryInterfaceFixture
{
    use TraitFixture;
    use SecondaryTraitFixture;

    /**
     * A constant defined in the child class.
     */
    public const string CHILD_CONST = 'child_value';

    /**
     * A property defined in the child class.
     */
    public string $childProperty = 'child_default';

    /**
     * Implements the abstract method from BaseClassFixture.
     *
     * @param string $input The input string.
     *
     * @return string The input in uppercase.
     */
    public function processInput(string $input): string
    {
        return strtoupper($input);
    }

    /**
     * Overrides the parent's getIdentifier method.
     *
     * @return string The child class identifier.
     */
    public function getIdentifier(): string
    {
        return 'ChildClass';
    }

    /**
     * A method only in the child class.
     *
     * @return int A child-specific value.
     */
    public function childOnlyMethod(): int
    {
        return 42;
    }

    /**
     * Implements InterfaceFixture::getName.
     */
    public function getName(): string
    {
        return 'ChildClass';
    }

    /**
     * Implements InterfaceFixture::setValue.
     */
    public function setValue(string $key, mixed $value): void {}

    /**
     * Implements InterfaceFixture::hasKey.
     */
    public function hasKey(string $key): bool
    {
        return false;
    }

    /**
     * Implements InterfaceFixture::create.
     */
    public static function create(): static
    {
        return new static;
    }

    /**
     * Implements SecondaryInterfaceFixture::process.
     */
    public function process(array $data): array
    {
        return $data;
    }
}

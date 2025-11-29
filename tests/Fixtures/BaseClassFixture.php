<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * Base class for testing inheritance features.
 *
 * @api
 */
abstract class BaseClassFixture
{
    /**
     * A constant in the base class.
     */
    public const string BASE_CONST = 'base_value';

    /**
     * A protected constant.
     */
    protected const int PROTECTED_BASE_CONST = 100;

    /**
     * A public property in the base class.
     */
    public string $basePublicProperty = 'base_public';

    /**
     * A protected property in the base class.
     */
    protected int $baseProtectedProperty = 0;

    /**
     * A private property in the base class (not inherited).
     */
    private string $basePrivateProperty = 'base_private';

    /**
     * A public method in the base class.
     *
     * @return string The base name.
     */
    public function getBaseName(): string
    {
        return 'BaseName';
    }

    /**
     * A protected method in the base class.
     */
    protected function protectedBaseMethod(): void {}

    /**
     * An abstract method to be implemented by child classes.
     *
     * @param string $input The input string.
     *
     * @return string The processed output.
     */
    abstract public function processInput(string $input): string;

    /**
     * A method that will be overridden.
     *
     * @return string The class identifier.
     */
    public function getIdentifier(): string
    {
        return 'BaseClass';
    }

    /**
     * A final method that cannot be overridden.
     *
     * @return string A fixed value.
     */
    final public function getFinalValue(): string
    {
        return 'immutable';
    }
}

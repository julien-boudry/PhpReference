<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A comprehensive fixture class for testing ClassWrapper features.
 *
 * This class contains various types of properties, methods, and constants
 * with different visibility and modifiers to test the reflection wrappers.
 *
 * @api
 */
class ClassFixture
{
    // ========== Constants ==========

    /**
     * A public string constant.
     */
    public const string PUBLIC_CONST = 'public_value';

    /**
     * A protected constant.
     */
    protected const PROTECTED_CONST = 'protected_value';

    /**
     * A private constant.
     */
    private const PRIVATE_CONST = 'private_value';

    /**
     * A public int constant.
     */
    public const int INT_CONST = 42;

    /**
     * A public array constant.
     */
    public const array ARRAY_CONST = ['a', 'b', 'c'];

    // ========== Static Properties ==========

    /**
     * A public static string property.
     */
    public static string $publicStaticProperty = 'static_value';

    /**
     * A protected static property.
     */
    protected static int $protectedStaticProperty = 100;

    /**
     * A private static property.
     */
    private static bool $privateStaticProperty = true;

    // ========== Instance Properties ==========

    /**
     * A public string property.
     */
    public string $publicProperty = 'default_value';

    /**
     * A public readonly property.
     */
    public readonly int $readonlyProperty;

    /**
     * A protected property with no default.
     */
    protected string $protectedProperty;

    /**
     * A private array property.
     */
    private array $privateProperty = [];

    /**
     * A nullable property.
     */
    public ?string $nullableProperty = null;

    // ========== Constructor ==========

    /**
     * Constructor with promoted properties.
     *
     * @param string $promotedPublic A promoted public property.
     * @param int $promotedProtected A promoted protected property.
     */
    public function __construct(
        public string $promotedPublic = 'promoted',
        protected int $promotedProtected = 0
    ) {
        $this->readonlyProperty = 42;
    }

    // ========== Static Methods ==========

    /**
     * A public static method.
     *
     * @return string Returns a static greeting.
     */
    public static function publicStaticMethod(): string
    {
        return 'Hello from static method';
    }

    /**
     * A protected static method.
     */
    protected static function protectedStaticMethod(): void {}

    /**
     * A private static method.
     */
    private static function privateStaticMethod(): bool
    {
        return true;
    }

    // ========== Instance Methods ==========

    /**
     * A public method with no parameters.
     *
     * @return string A simple greeting.
     */
    public function publicMethod(): string
    {
        return 'Hello';
    }

    /**
     * A public method with parameters.
     *
     * @param string $name The name to greet.
     * @param int $times How many times to repeat.
     *
     * @return string The greeting message.
     */
    public function publicMethodWithParams(string $name, int $times = 1): string
    {
        return str_repeat("Hello, {$name}! ", $times);
    }

    /**
     * A protected method.
     */
    protected function protectedMethod(): void {}

    /**
     * A private method.
     */
    private function privateMethod(): array
    {
        return [];
    }

    /**
     * A final public method.
     */
    final public function finalMethod(): void {}

    /**
     * A method that returns void.
     */
    public function voidMethod(): void {}

    /**
     * A method returning mixed type.
     */
    public function mixedMethod(): mixed
    {
        return null;
    }

    /**
     * A method returning self.
     */
    public function selfMethod(): self
    {
        return $this;
    }

    /**
     * A method returning static.
     */
    public function staticReturnMethod(): static
    {
        return $this;
    }

    // ========== Internal method (should be excluded) ==========

    /**
     * An internal method that should not be part of the public API.
     *
     * @internal
     */
    public function internalMethod(): void {}
}

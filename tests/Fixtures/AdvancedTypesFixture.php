<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

use Stringable;

/**
 * Fixture for testing advanced type features.
 *
 * Contains union types, intersection types, nullable, variadic, and reference parameters.
 *
 * @api
 */
class AdvancedTypesFixture
{
    /**
     * Property with union type.
     */
    public string|int $unionProperty = 'default';

    /**
     * Property with nullable union type.
     */
    public string|int|null $nullableUnionProperty = null;

    /**
     * Method with union type return.
     *
     * @return string|int A string or integer value.
     */
    public function unionReturn(): string|int
    {
        return 'string';
    }

    /**
     * Method with nullable return type.
     *
     * @return string|null A string or null.
     */
    public function nullableReturn(): ?string
    {
        return null;
    }

    /**
     * Method with union type parameter.
     *
     * @param string|int $value A string or integer value.
     */
    public function unionParam(string|int $value): void {}

    /**
     * Method with variadic parameter.
     *
     * @param string ...$values Multiple string values.
     *
     * @return string[] All values.
     */
    public function variadicParam(string ...$values): array
    {
        return $values;
    }

    /**
     * Method with typed variadic parameter.
     *
     * @param int ...$numbers Multiple numbers.
     *
     * @return int The sum.
     */
    public function variadicIntParam(int ...$numbers): int
    {
        return array_sum($numbers);
    }

    /**
     * Method with reference parameter.
     *
     * @param int $counter The counter to increment.
     */
    public function referenceParam(int &$counter): void
    {
        $counter++;
    }

    /**
     * Method with multiple reference parameters.
     *
     * @param string $a First string.
     * @param string $b Second string.
     */
    public function swapStrings(string &$a, string &$b): void
    {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }

    /**
     * Method with intersection type parameter.
     *
     * @param Stringable&ClassFixture $value A value that is both Stringable and ClassFixture.
     */
    public function intersectionParam(Stringable&ClassFixture $value): void {}

    /**
     * Method with mixed parameter and return.
     *
     * @param mixed $value Any value.
     *
     * @return mixed The same value.
     */
    public function mixedParam(mixed $value): mixed
    {
        return $value;
    }

    /**
     * Method with callable parameter.
     *
     * @param callable $callback A callback function.
     * @param mixed $value The value to pass to callback.
     *
     * @return mixed The callback result.
     */
    public function callableParam(callable $callback, mixed $value): mixed
    {
        return $callback($value);
    }

    /**
     * Method with iterable parameter.
     *
     * @param iterable<mixed> $items Items to iterate.
     *
     * @return array<mixed> Array of items.
     */
    public function iterableParam(iterable $items): array
    {
        return iterator_to_array($items);
    }

    /**
     * Method with array type hints.
     *
     * @param array<string, int> $map A string-to-int map.
     *
     * @return array<int, string> Reversed map.
     */
    public function arrayTypeHinted(array $map): array
    {
        return array_flip($map);
    }

    /**
     * Method returning never (always throws).
     *
     * @throws \RuntimeException Always thrown.
     */
    public function neverReturn(): never
    {
        throw new \RuntimeException('This method never returns');
    }

    /**
     * Method with false return type.
     *
     * @return string|false A string or false on failure.
     */
    public function stringOrFalse(): string|false
    {
        return false;
    }

    /**
     * Method with null return type.
     *
     * @return string|null A string or null.
     */
    public function stringOrNull(): string|null
    {
        return null;
    }

    /**
     * Method with true return type.
     *
     * @return true Always returns true.
     */
    public function trueReturn(): true
    {
        return true;
    }

    /**
     * Method with multiple default values.
     *
     * @param string $required Required parameter.
     * @param int $withDefault Parameter with int default.
     * @param array $arrayDefault Parameter with array default.
     * @param bool $boolDefault Parameter with bool default.
     * @param ?string $nullDefault Parameter with null default.
     */
    public function multipleDefaults(
        string $required,
        int $withDefault = 42,
        array $arrayDefault = ['a', 'b'],
        bool $boolDefault = true,
        ?string $nullDefault = null
    ): void {}
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

if (!\function_exists(__NAMESPACE__ . '\\testHelperFunction')) {
    /**
     * A test function for testing function discovery.
     *
     * @param string $input The input string to transform.
     *
     * @return string The input in uppercase.
     */
    function testHelperFunction(string $input): string
    {
        return strtoupper($input);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\anotherTestFunction')) {
    /**
     * Another test function.
     *
     * @param int $value The value to double.
     *
     * @return int The doubled value.
     */
    function anotherTestFunction(int $value): int
    {
        return $value * 2;
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionWithOptionalParams')) {
    /**
     * A function with optional parameters.
     *
     * @param string $required The required parameter.
     * @param int $optional An optional integer parameter.
     * @param array $defaultArray An array with default value.
     *
     * @return string A formatted string.
     */
    function functionWithOptionalParams(
        string $required,
        int $optional = 42,
        array $defaultArray = ['a', 'b']
    ): string {
        return "{$required}: {$optional}";
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionWithVariadic')) {
    /**
     * A function with variadic parameter.
     *
     * @param string ...$items Multiple string items.
     *
     * @return int The count of items.
     */
    function functionWithVariadic(string ...$items): int
    {
        return \count($items);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionWithReference')) {
    /**
     * A function that modifies a reference parameter.
     *
     * @param int $counter The counter to increment.
     */
    function functionWithReference(int &$counter): void
    {
        $counter++;
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionThatThrows')) {
    /**
     * A function that throws an exception.
     *
     *
     * @param int $value The value to check.
     *
     * @throws \InvalidArgumentException When value is negative.
     *
     * @return int The same value if positive.
     */
    function functionThatThrows(int $value): int
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Value must be non-negative');
        }

        return $value;
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionWithUnionType')) {
    /**
     * A function with union type return.
     *
     * @param bool $returnString Whether to return string.
     *
     * @return string|int Either a string or an integer.
     */
    function functionWithUnionType(bool $returnString): string|int
    {
        return $returnString ? 'string' : 42;
    }
}

if (!\function_exists(__NAMESPACE__ . '\\functionWithNullableReturn')) {
    /**
     * A function with nullable return type.
     *
     * @param bool $returnNull Whether to return null.
     *
     * @return string|null A string or null.
     */
    function functionWithNullableReturn(bool $returnNull): ?string
    {
        return $returnNull ? null : 'value';
    }
}

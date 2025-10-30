<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

if (!\function_exists(__NAMESPACE__ . '\\testHelperFunction')) {
    /**
     * A test function for testing function discovery.
     */
    function testHelperFunction(string $input): string
    {
        return strtoupper($input);
    }
}

if (!\function_exists(__NAMESPACE__ . '\\anotherTestFunction')) {
    /**
     * Another test function.
     */
    function anotherTestFunction(int $value): int
    {
        return $value * 2;
    }
}

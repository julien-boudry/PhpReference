<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * An interface fixture for testing InterfaceWrapper.
 *
 * Contains interface constants and method signatures.
 *
 * @api
 */
interface InterfaceFixture
{
    /**
     * An interface constant.
     */
    public const string INTERFACE_CONST = 'interface_value';

    /**
     * Another interface constant with int type.
     */
    public const int VERSION = 1;

    /**
     * Get the name.
     *
     * @return string The name value.
     */
    public function getName(): string;

    /**
     * Set a value.
     *
     * @param string $key The key to set.
     * @param mixed $value The value to set.
     */
    public function setValue(string $key, mixed $value): void;

    /**
     * Check if a key exists.
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key exists.
     */
    public function hasKey(string $key): bool;

    /**
     * A static interface method.
     *
     * @return static A new instance.
     */
    public static function create(): static;
}

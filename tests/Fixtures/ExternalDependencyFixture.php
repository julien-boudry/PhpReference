<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

use ArrayIterator;
use RuntimeException;
use Generator;

/**
 * Fixture for testing classes that depend on external (non-indexed) classes.
 *
 * This fixture tests the scenario where:
 * - A class extends an external class (Exception)
 * - Methods have parameters with external types
 * - Methods have return types that reference external classes
 * - @throws tags reference external exception classes
 *
 * @api
 */
class ExternalDependencyFixture extends \Exception
{
    /**
     * Method with an external class as parameter type.
     *
     * @param ArrayIterator<int, string> $iterator The iterator to process.
     *
     * @return int The count of items.
     */
    public function processIterator(ArrayIterator $iterator): int
    {
        return $iterator->count();
    }

    /**
     * Method returning an external class type.
     *
     * @return ArrayIterator<int, string> A new iterator.
     */
    public function createIterator(): ArrayIterator
    {
        return new ArrayIterator([]);
    }

    /**
     * Method that throws an external exception.
     *
     * @throws RuntimeException When something goes wrong.
     */
    public function methodThatThrowsExternalException(): void
    {
        throw new RuntimeException('External exception');
    }

    /**
     * Method that throws multiple external exceptions.
     *
     * @throws RuntimeException When runtime error occurs.
     * @throws \InvalidArgumentException When argument is invalid.
     * @throws \LogicException When logic error occurs.
     */
    public function methodThatThrowsMultipleExternalExceptions(): void
    {
        throw new RuntimeException('Error');
    }

    /**
     * Method with union type including external class.
     *
     * @param ArrayIterator<int, string>|\Traversable<int, string> $data The data to process.
     *
     * @return int The count.
     */
    public function processTraversable(ArrayIterator|\Traversable $data): int
    {
        return iterator_count($data);
    }

    /**
     * Method returning a Generator (external type).
     *
     * @return Generator<int, string> A generator of strings.
     */
    public function getGenerator(): Generator
    {
        yield 'hello';
        yield 'world';
    }

    /**
     * Method with nullable external type.
     *
     * @param ArrayIterator<int, string>|null $iterator Optional iterator.
     *
     * @return int|null The count or null.
     */
    public function processNullableIterator(?ArrayIterator $iterator): ?int
    {
        return $iterator?->count();
    }
}

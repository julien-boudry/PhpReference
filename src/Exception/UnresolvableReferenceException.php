<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Exception thrown when a reference to a class, method, or property cannot be resolved.
 *
 * This exception is thrown when attempting to resolve PHPDoc @see or @throws
 * references that point to elements not found in the code index. Common causes:
 *
 * - Reference to a class outside the indexed namespace
 * - Typo in the reference path
 * - Reference to a method or property that doesn't exist
 * - Invalid reference format (missing '::' separator)
 *
 * The exception includes the unresolvable reference string for debugging.
 *
 * @see CodeIndex::getClassElement() Where this exception is typically thrown
 */
class UnresolvableReferenceException extends PhpReferenceException
{
    /**
     * Creates a new exception for an unresolvable reference.
     *
     * @param $reference The reference that could not be resolved
     * @param $message   Optional custom message (defaults to generic message)
     * @param $previous  Previous exception for chaining
     */
    public function __construct(
        public readonly string $reference,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message ?: "Unable to resolve reference: {$reference}",
            previous: $previous
        );
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Thrown when a reference (class, method, property) cannot be resolved.
 */
class UnresolvableReferenceException extends PhpReferenceException
{
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

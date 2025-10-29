<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Thrown when attempting an operation on a wrapper that doesn't support it.
 */
class UnsupportedOperationException extends PhpReferenceException
{
    public function __construct(
        public readonly string $operation,
        public readonly string $wrapperType,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: "Operation '{$operation}' is not supported on {$wrapperType}",
            previous: $previous
        );
    }
}

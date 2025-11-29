<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Exception thrown when attempting an operation not supported by a wrapper type.
 *
 * Different reflection wrapper types support different operations. For example,
 * ParameterWrapper doesn't support getModifierNames() because parameters don't
 * have visibility modifiers. This exception is thrown when calling such
 * unsupported operations.
 *
 * The exception includes both the operation name and wrapper type for debugging.
 *
 * @see ReflectionWrapper For the base wrapper class where operations are defined
 */
class UnsupportedOperationException extends PhpReferenceException
{
    /**
     * Creates a new exception for an unsupported operation.
     *
     * @param string          $operation   The name of the unsupported operation
     * @param string          $wrapperType The class name of the wrapper that doesn't support it
     * @param \Throwable|null $previous    Previous exception for chaining
     */
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

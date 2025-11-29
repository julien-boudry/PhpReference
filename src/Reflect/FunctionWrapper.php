<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, IsFunction};
use ReflectionFunction;

/**
 * Wrapper for PHP standalone function reflection with enhanced documentation capabilities.
 *
 * Provides access to function parameters, return types, exceptions, and PHPDoc
 * information. Also generates function signatures for documentation display.
 *
 * Unlike MethodWrapper, FunctionWrapper extends ReflectionWrapper directly since
 * functions are not class members.
 *
 * @see ReflectionWrapper For base functionality
 * @see IsFunction For parameter and return type handling
 * @see CanThrow For @throws tag handling
 */
class FunctionWrapper extends ReflectionWrapper implements SignatureInterface, WritableInterface
{
    use CanThrow;
    use IsFunction;

    /**
     * The underlying ReflectionFunction.
     */
    public ReflectionFunction $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Returns the path for this function's documentation page.
     */
    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/function_{$this->name}.md";
    }

    /**
     * Generates the function signature for documentation.
     *
     * @param bool $withClassName Ignored for functions (maintained for interface compatibility)
     */
    public function getSignature(bool $withClassName = false): string
    {
        return 'function ' . $this->getFunctionPartSignature();
    }
}

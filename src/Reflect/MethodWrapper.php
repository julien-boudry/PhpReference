<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, IsFunction};
use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use ReflectionMethod;

/**
 * Wrapper for PHP method reflection with enhanced documentation capabilities.
 *
 * Provides access to method parameters, return types, exceptions, and PHPDoc
 * information. Also generates method signatures for documentation display.
 *
 * @see ClassElementWrapper For common class member functionality
 * @see IsFunction For parameter and return type handling
 * @see CanThrow For @throws tag handling
 */
class MethodWrapper extends ClassElementWrapper implements SignatureInterface, WritableInterface
{
    use CanThrow;
    use IsFunction;

    /**
     * The underlying ReflectionMethod.
     */
    public ReflectionMethod $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Returns the path for this method's documentation page.
     */
    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/method_{$this->name}.md";
    }

    /**
     * Generates the method signature for documentation.
     *
     * @param $withClassName Whether to include the class name prefix
     */
    public function getSignature(bool $withClassName = false): string
    {
        return $this->getModifierNames()
                . ' function '
                . (! $withClassName ? $this->inDocParentWrapper->shortName : '')
                . (! $withClassName ? ($this->reflection->isStatic() ? '::' : '->') : '')
                . $this->getFunctionPartSignature();
    }
}

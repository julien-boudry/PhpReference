<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use ReflectionClassConstant;

/**
 * Wrapper for PHP class constant reflection with enhanced documentation capabilities.
 *
 * Provides access to constant visibility, type (PHP 8.3+), and value.
 * Note: Class constants do not have their own documentation pages; they are
 * documented on the class page.
 *
 * @see ClassElementWrapper For common class member functionality
 */
class ClassConstantWrapper extends ClassElementWrapper implements SignatureInterface
{
    /**
     * The underlying ReflectionClassConstant.
     */
    public ReflectionClassConstant $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Generates the constant signature for documentation.
     *
     * Includes visibility modifiers, type (if declared), name, and value.
     *
     * @param bool $withClassName Whether to include the class name prefix
     */
    public function getSignature(bool $withClassName = false): string
    {
        $type = $this->reflection->getType() ? ' ' . ((string) $this->reflection->getType()) . ' ' : ' ';
        $value = self::formatValue($this->reflection->getValue());

        $name = $this->name;

        if ($withClassName) {
            $name = $this->inDocParentWrapper->shortName . '::' . $name;
        }

        return "{$this->getModifierNames()} const{$type}{$name} = {$value}";
    }
}

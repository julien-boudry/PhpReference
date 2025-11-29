<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, HasType};
use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use ReflectionProperty;

/**
 * Wrapper for PHP property reflection with enhanced documentation capabilities.
 *
 * Provides access to property types, default values, visibility (including
 * asymmetric visibility in PHP 8.4+), and virtual property detection.
 *
 * @see ClassElementWrapper For common class member functionality
 * @see HasType For type information handling
 * @see CanThrow For @throws tag handling (property hooks can throw)
 */
class PropertyWrapper extends ClassElementWrapper implements SignatureInterface, WritableInterface
{
    use CanThrow;
    use HasType;

    /**
     * The underlying ReflectionProperty.
     */
    public ReflectionProperty $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Returns the path for this property's documentation page.
     *
     * Page names include prefixes for static and virtual properties.
     */
    public function getPagePath(): string
    {
        $static = $this->reflection->isStatic() ? 'static_' : '';
        $virtual = $this->isVirtual() ? 'virtual_' : '';

        return $this->getPageDirectory() . "/{$static}{$virtual}property_{$this->name}.md";
    }

    /**
     * Checks if this is a virtual property (PHP 8.4+ property hooks).
     */
    public function isVirtual(): bool
    {
        return $this->reflection->isVirtual();
    }

    /**
     * Generates the property signature for documentation.
     *
     * Includes visibility modifiers, asymmetric set visibility (PHP 8.4+),
     * type, name, and default value.
     *
     * @param $withClassName Whether to include the class name prefix
     */
    public function getSignature(bool $withClassName = false): string
    {
        $type = ' ' . $this->getType() . ' ';

        $setVisibility = '';

        if ($this->reflection->isProtectedSet()) {
            $setVisibility = ' protected(set)';
        } elseif ($this->reflection->isPrivateSet()) {
            $setVisibility = ' private(set)';
        }

        $defaultValue = $this->reflection->hasDefaultValue() ? ' = ' . self::formatValue($this->reflection->getDefaultValue()) : '';

        $name = $this->name;

        if ($withClassName) {
            $name = $this->inDocParentWrapper->shortName . ($type === 'static' ? '::$' : '->') . $name;
        } else {
            $name = '$' . $name;
        }

        return "{$this->getModifierNames()}{$setVisibility}{$type}{$name}{$defaultValue}";
    }
}

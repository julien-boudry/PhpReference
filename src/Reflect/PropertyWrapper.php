<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class PropertyWrapper extends ClassElementWrapper implements WritableInterface
{
    public ReflectionProperty $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(
        ReflectionProperty $reflectionProperty,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionProperty, $classWrapper);
    }

    public function getPagePath(): string
    {
        $static = $this->reflection->isStatic() ? 'static_' : '';
        $virtual = $this->reflection->isVirtual() ? 'virtual_' : '';

        return $this->getPageDirectory() . "/{$static}{$virtual}property_{$this->name}.md";
    }

    public function getSignature(): string
    {
        $virtual = $this->reflection->isVirtual() ? 'virtual ' : '';
        $static = $this->reflection->isStatic() ? 'static ' : '';

        $propertyType = $this->reflection->getType();

        $type = $propertyType?->allowsNull() ? '?' : '';
        $type .= (string) $propertyType . ' ';

        $defaultValue = $this->reflection->isDefault() ? ' = ' . var_export($this->reflection->getDefaultValue(), true) : '';
        $defaultValue = str_replace('NULL', 'null', $defaultValue);

        return "{$static}{$virtual}{$type}\${$this->name}{$defaultValue}";
    }
}
<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Structure\HasType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class PropertyWrapper extends ClassElementWrapper implements WritableInterface, SignatureInterface
{
    use HasType;

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
        $type = ' ' . $this->getType() . ' ';

        $setVisibility = '';

        if ($this->reflection->isProtectedSet()) {
            $setVisibility = ' protected(set)';
        } elseif ($this->reflection->isPrivateSet()) {
            $setVisibility = ' private(set)';
        }

        $defaultValue = $this->reflection->hasDefaultValue() ? ' = ' . self::formatValue($this->reflection->getDefaultValue()) : '';

        return "{$this->getModifierNames()}{$setVisibility}{$type}\${$this->name}{$defaultValue}";
    }
}
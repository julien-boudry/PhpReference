<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\Reflect\Structure\CanThrow;
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
    use CanThrow;

    public ReflectionProperty $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function getPagePath(): string
    {
        $static = $this->reflection->isStatic() ? 'static_' : '';
        $virtual = $this->isVirtual() ? 'virtual_' : '';

        return $this->getPageDirectory() . "/{$static}{$virtual}property_{$this->name}.md";
    }

    public function isVirtual(): bool
    {
        return $this->reflection->isVirtual();
    }

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
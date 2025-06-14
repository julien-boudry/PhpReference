<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class ClassConstantWrapper extends ClassElementWrapper implements SignatureInterface
{
    public ReflectionClassConstant $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

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
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

    public function __construct(
        ReflectionClassConstant $reflectionClassConstant,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionClassConstant, $classWrapper);
    }

    public function getSignature(): string
    {
        $type = $this->reflection->getType() ? ' ' . ((string) $this->reflection->getType()) . ' ' : ' ';
        $value = self::formatValue($this->reflection->getValue());

        return "{$this->getModifierNames()} const{$type}{$this->name} = {$value}";
    }
}
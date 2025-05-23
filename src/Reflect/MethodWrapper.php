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

class MethodWrapper extends ClassElementWrapper
{
    public ReflectionMethod $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(
        ReflectionMethod $reflectionMethod,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionMethod, $classWrapper);
    }
}
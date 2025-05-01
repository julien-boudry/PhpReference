<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

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
    public function __construct(
        public readonly ReflectionMethod $reflectionMethod,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionMethod, $classWrapper);
    }
}
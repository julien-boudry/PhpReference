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

class PropertyWrapper extends ClassElementWrapper
{
    public function __construct(
        public readonly ReflectionProperty $reflectionProperty,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionProperty, $classWrapper);
    }
}
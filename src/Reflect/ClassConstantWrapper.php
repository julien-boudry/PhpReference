<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class ClassConstantWrapper extends ClassElementWrapper
{
    public function __construct(
        ReflectionClassConstant $reflectionClassConstant,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionClassConstant, $classWrapper);
    }
}
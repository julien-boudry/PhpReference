<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Structure\CanThrow;
use JulienBoudry\PhpReference\Reflect\Structure\IsFunction;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class FunctionWrapper extends ReflectionWrapper
{
    use IsFunction;
    use CanThrow;

    public ReflectionFunction $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }
}
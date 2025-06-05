<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\ClassElementWrapper;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\FunctionWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
use ReflectionFunctionAbstract;

abstract class Base
{
    protected function baseExclusion(ReflectionWrapper $reflectionWrapper): bool
    {
        if ($reflectionWrapper->hasInternalTag) {
            return false;
        }

        if ($reflectionWrapper instanceof ClassWrapper || $reflectionWrapper instanceof MethodWrapper || $reflectionWrapper instanceof FunctionWrapper) {
            if (!$reflectionWrapper->isUserDefined()) {
                return false;
            }
        }

        return true;
    }
}
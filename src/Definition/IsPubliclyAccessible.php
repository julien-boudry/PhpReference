<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\ClassElementWrapper;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

class IsPubliclyAccessible extends Base implements PublicApiDefinitionInterface
{
    public function isPartOfPublicApi(ReflectionWrapper $reflectionWrapper): bool
    {
        if (!$this->baseExclusion($reflectionWrapper)) {
            return false;
        }

        if ($reflectionWrapper instanceof ClassElementWrapper) {
            if (!$reflectionWrapper->isPublic()) {
                return false;
            }

            return true;
        }

        return true;
    }
}
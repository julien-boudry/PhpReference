<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\ClassElementWrapper;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\FunctionWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
use ReflectionFunctionAbstract;

class HasTagApi extends Base implements PublicApiDefinitionInterface
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
        }

        if ($reflectionWrapper instanceof ClassWrapper) {
            if (!empty($reflectionWrapper->getAllApiConstants()) || !empty($reflectionWrapper->getAllApiProperties()) || !empty($reflectionWrapper->getAllApiMethods())) {
                return true;
            }
        }

        return $reflectionWrapper->hasApiTag;
    }
}
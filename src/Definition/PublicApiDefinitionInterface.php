<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

interface PublicApiDefinitionInterface
{
    public function isPartOfPublicApi(ReflectionWrapper $reflectionWrapper): bool;
}

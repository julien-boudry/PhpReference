<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

class NamespaceWrapper
{
    /**
     * @param array<string, ClassWrapper> $classes
     */
    public function __construct(
        public readonly string $namespace,
        public readonly array $classes,
    ) {}
}

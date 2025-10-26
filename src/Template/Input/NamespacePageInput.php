<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, NamespaceWrapper};

class NamespacePageInput
{
    /** @var array<string, ClassWrapper> */
    public readonly array $classes;

    /** @var array<string, ClassWrapper> */
    public readonly array $enums;

    public function __construct(
        public readonly NamespaceWrapper $namespace,
    ) {
        $classes = [];
        $enums = [];

        foreach ($this->namespace->classes as $class) {
            match ($class::TYPE) {
                'class' => $classes[$class->shortName] = $class,
                'enum' => $enums[$class->shortName] = $class,
                default => null,
            };
        }

        $this->classes = $classes;
        $this->enums = $enums;
    }
}

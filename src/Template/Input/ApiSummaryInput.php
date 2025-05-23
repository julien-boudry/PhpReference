<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

class ApiSummaryInput
{
    /** @var array<string, array<ClassWrapper>> */
    public readonly array $namespaces;

    public function __construct(
        /** @var array<ClassWrapper> */
        array $classes,
    ) {
        $namespaces = [];

        foreach ($classes as $class) {
            $namespaces[$class->reflection->getNamespaceName()][$class->shortName] = $class;
        }

        $this->namespaces = $namespaces;
    }
}
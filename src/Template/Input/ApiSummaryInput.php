<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

class ApiSummaryInput
{
    /** @var array<string, array<string, ClassWrapper[]>> */
    public readonly array $namespaces;

    public function __construct(
        /** @var array<string, ClassWrapper[]> */
        array $classes,
    ) {
        $namespaces = [];

        foreach ($classes as $class) {
            if (!isset($namespaces[$class->reflection->getNamespaceName()])) {
                $namespaces[$class->reflection->getNamespaceName()] = ['class' => [], 'enum' => [], 'trait' => []];
            }

            $namespaces[$class->reflection->getNamespaceName()][$class::TYPE][$class->shortName] = $class;
        }

        $this->namespaces = $namespaces;
    }
}
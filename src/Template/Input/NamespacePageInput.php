<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, NamespaceWrapper};

/**
 * Template input for namespace index documentation pages.
 *
 * Prepares namespace data for the namespace_page.latte template, organizing
 * classes and enums into separate arrays for display.
 *
 * @see NamespacePageWriter For where this input is used
 */
class NamespacePageInput
{
    /**
     * Classes in this namespace (excludes enums), keyed by short name.
     *
     * @var array<string, ClassWrapper>
     */
    public readonly array $classes;

    /**
     * Enums in this namespace, keyed by short name.
     *
     * @var array<string, ClassWrapper>
     */
    public readonly array $enums;

    /**
     * Creates a new namespace page input.
     *
     * Organizes API elements into classes and enums.
     *
     * @param $namespaceWrapper The namespace wrapper to document
     */
    public function __construct(
        public readonly NamespaceWrapper $namespaceWrapper,
    ) {
        $classes = [];
        $enums = [];

        foreach ($this->namespaceWrapper->apiClasses as $class) {
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

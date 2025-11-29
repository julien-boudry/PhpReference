<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

/**
 * Template input for class documentation pages.
 *
 * Prepares class data for the class_page.latte template, including
 * the formatted type name (Class, Interface, Trait, Enum).
 *
 * @see ClassPageWriter For where this input is used
 */
class ClassPageInput extends AbstractElementInput
{
    /**
     * The formatted type name (e.g., "Class", "Interface", "Enum").
     */
    public readonly string $type;

    /**
     * Creates a new class page input.
     *
     * @param $class The class wrapper to document
     */
    public function __construct(
        public readonly ClassWrapper $class,
    ) {
        $this->reflectionWrapper = $class;

        $this->type = ucfirst($class::TYPE);
    }
}

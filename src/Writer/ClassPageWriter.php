<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Template\Input\ClassPageInput;

/**
 * Writer for generating class documentation pages.
 *
 * This writer creates the main documentation page for a class, interface,
 * trait, or enum. The generated page includes:
 * - Class signature and modifiers
 * - Description from PHPDoc
 * - List of methods with links to individual method pages
 * - List of properties with links to individual property pages
 * - List of constants
 * - Inheritance and interface information
 *
 * @see ClassWrapper For the class data source
 * @see ClassPageInput For the template input data
 */
class ClassPageWriter extends AbstractWriter
{
    /**
     * Creates a new class page writer.
     *
     * @param ClassWrapper $class The class wrapper to generate documentation for
     */
    public function __construct(public readonly ClassWrapper $class)
    {
        $this->writePath = $class->getPagePath();

        parent::__construct();
    }

    /**
     * Generates the class documentation content using the class_page template.
     */
    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/class_page.latte',
            params : new ClassPageInput(
                class: $this->class
            ),
        );
    }
}

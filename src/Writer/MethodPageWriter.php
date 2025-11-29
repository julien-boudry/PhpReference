<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Template\Input\MethodPageInput;

/**
 * Writer for generating method documentation pages.
 *
 * This writer creates individual documentation pages for each public method.
 * The generated page includes:
 * - Method signature with visibility, parameters, and return type
 * - Full description from PHPDoc
 * - Parameter documentation
 * - Return value documentation
 * - Exceptions that may be thrown
 * - Related @see references
 * - Link to source code (if configured)
 *
 * @see MethodWrapper For the method data source
 * @see MethodPageInput For the template input data
 */
class MethodPageWriter extends AbstractWriter
{
    /**
     * Creates a new method page writer.
     *
     * @param $method The method wrapper to generate documentation for
     */
    public function __construct(public readonly MethodWrapper $method)
    {
        $this->writePath = $method->getPagePath();

        parent::__construct();
    }

    /**
     * Generates the method documentation content using the method_page template.
     */
    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/method_page.latte',
            params : new MethodPageInput(
                method: $this->method
            ),
        );
    }
}

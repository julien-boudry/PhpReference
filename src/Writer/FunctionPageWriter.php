<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\FunctionWrapper;
use JulienBoudry\PhpReference\Template\Input\FunctionPageInput;

/**
 * Writer for generating standalone function documentation pages.
 *
 * This writer creates documentation pages for standalone PHP functions
 * (not class methods). The generated page includes:
 * - Function signature with parameters and return type
 * - Full description from PHPDoc
 * - Parameter documentation
 * - Return value documentation
 * - Exceptions that may be thrown
 * - Related @see references
 * - Link to source code (if configured)
 *
 * @see FunctionWrapper For the function data source
 * @see FunctionPageInput For the template input data
 */
class FunctionPageWriter extends AbstractWriter
{
    /**
     * Creates a new function page writer.
     *
     * @param FunctionWrapper $function The function wrapper to generate documentation for
     */
    public function __construct(public readonly FunctionWrapper $function)
    {
        $this->writePath = $function->getPagePath();

        parent::__construct();
    }

    /**
     * Generates the function documentation content using the function_page template.
     */
    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/function_page.latte',
            params : new FunctionPageInput(
                function: $this->function
            ),
        );
    }
}

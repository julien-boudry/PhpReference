<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\PropertyWrapper;
use JulienBoudry\PhpReference\Template\Input\PropertyPageInput;

/**
 * Writer for generating property documentation pages.
 *
 * This writer creates individual documentation pages for each public property.
 * The generated page includes:
 * - Property signature with visibility and type
 * - Full description from PHPDoc
 * - Default value (if any)
 * - Property hooks information (PHP 8.4+)
 * - Related @see references
 * - Link to source code (if configured)
 *
 * @see PropertyWrapper For the property data source
 * @see PropertyPageInput For the template input data
 */
class PropertyPageWriter extends AbstractWriter
{
    /**
     * Creates a new property page writer.
     *
     * @param $property The property wrapper to generate documentation for
     */
    public function __construct(public readonly PropertyWrapper $property)
    {
        $this->writePath = $property->getPagePath();

        parent::__construct();
    }

    /**
     * Generates the property documentation content using the property_page template.
     */
    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/property_page.latte',
            params : new PropertyPageInput(
                property: $this->property
            ),
        );
    }
}

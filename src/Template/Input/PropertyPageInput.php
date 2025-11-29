<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\PropertyWrapper;

/**
 * Template input for property documentation pages.
 *
 * Prepares property data for the property_page.latte template.
 *
 * @see PropertyPageWriter For where this input is used
 */
class PropertyPageInput extends AbstractElementInput
{
    /**
     * Creates a new property page input.
     *
     * @param PropertyWrapper $property The property wrapper to document
     */
    public function __construct(
        public readonly PropertyWrapper $property,
    ) {
        $this->reflectionWrapper = $property;
    }
}

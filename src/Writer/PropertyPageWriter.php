<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\PropertyWrapper;
use JulienBoudry\PhpReference\Template\Input\PropertyPageInput;

class PropertyPageWriter extends AbstractWriter
{
    public function __construct(public readonly PropertyWrapper $property)
    {
        $this->writePath = $property->getPagePath();

        parent::__construct();
    }

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

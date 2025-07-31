<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Template\Input\MethodPageInput;

class MethodPageWriter extends AbstractWriter
{
    public function __construct(public readonly MethodWrapper $method)
    {
        $this->writePath = $method->getPagePath();

        parent::__construct();
    }

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

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\{FunctionWrapper};
use JulienBoudry\PhpReference\Template\Input\{FunctionPageInput};

class FunctionPageWriter extends AbstractWriter
{
    public function __construct(public readonly FunctionWrapper $function)
    {
        $this->writePath = $function->getPagePath();

        parent::__construct();
    }

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

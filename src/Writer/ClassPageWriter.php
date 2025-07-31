<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Template\Input\ClassPageInput;

class ClassPageWriter extends AbstractWriter
{
    public function __construct(public readonly ClassWrapper $class)
    {
        $this->writePath = $class->getPagePath();

        parent::__construct();
    }

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

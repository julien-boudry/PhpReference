<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

class ClassPageInput extends AbstractElementInput
{
    public readonly string $type;

    public function __construct(
        public readonly ClassWrapper $class,
    ) {
        $this->reflectionWrapper = $class;

        $this->type = ucfirst($class::TYPE);
    }
}
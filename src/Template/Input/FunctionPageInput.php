<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\FunctionWrapper;

class FunctionPageInput extends AbstractElementInput
{
    public function __construct(
        public readonly FunctionWrapper $function,
    ) {
        $this->reflectionWrapper = $function;
    }
}

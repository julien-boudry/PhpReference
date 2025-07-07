<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\MethodWrapper;

class MethodPageInput extends AbstractElementInput
{
    public function __construct(
        public readonly MethodWrapper $method,
    ) {
        $this->reflectionWrapper = $method;
    }
}

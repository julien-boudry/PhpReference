<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;

class MethodPageInput
{
    public function __construct(
        public readonly MethodWrapper $method,
    ) {}
}
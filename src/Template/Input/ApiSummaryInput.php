<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;

class ApiSummaryInput
{
    public function __construct(
        /** @var array<ClassWrapper> */
        public readonly array $classes,
    ) {}
}
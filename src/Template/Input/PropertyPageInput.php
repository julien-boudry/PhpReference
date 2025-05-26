<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Reflect\PropertyWrapper;

class PropertyPageInput extends AbstractElementInput
{
    public function __construct(
        public readonly PropertyWrapper $property,
    ) {
        $this->reflectionWrapper = $property;
    }
}
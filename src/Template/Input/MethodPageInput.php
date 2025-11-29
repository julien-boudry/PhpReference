<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\MethodWrapper;

/**
 * Template input for method documentation pages.
 *
 * Prepares method data for the method_page.latte template.
 *
 * @see MethodPageWriter For where this input is used
 */
class MethodPageInput extends AbstractElementInput
{
    /**
     * Creates a new method page input.
     *
     * @param $method The method wrapper to document
     */
    public function __construct(
        public readonly MethodWrapper $method,
    ) {
        $this->reflectionWrapper = $method;
    }
}

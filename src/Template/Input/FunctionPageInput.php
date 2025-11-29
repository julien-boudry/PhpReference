<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\FunctionWrapper;

/**
 * Template input for standalone function documentation pages.
 *
 * Prepares function data for the function_page.latte template.
 *
 * @see FunctionPageWriter For where this input is used
 */
class FunctionPageInput extends AbstractElementInput
{
    /**
     * Creates a new function page input.
     *
     * @param $function The function wrapper to document
     */
    public function __construct(
        public readonly FunctionWrapper $function,
    ) {
        $this->reflectionWrapper = $function;
    }
}

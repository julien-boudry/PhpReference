<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\{UrlLinker, Util};

/**
 * Trait for elements that have a type declaration.
 *
 * This trait is used by PropertyWrapper and ParameterWrapper to provide
 * functionality for accessing type information and generating Markdown
 * representations with automatic cross-linking.
 *
 * @mixin \JulienBoudry\PhpReference\Reflect\PropertyWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\ParameterWrapper
 */
trait HasType
{
    /**
     * Returns the type of the element as a string.
     *
     * For union or intersection types, returns the full composite type string.
     *
     * @return string|null The type name, or null if no type is declared
     */
    public function getType(): ?string
    {
        $type = $this->reflection->getType();

        return $type ? (string) $type : null;
    }

    /**
     * Returns the type as Markdown with automatic cross-linking.
     *
     * Types that reference classes in the code index are converted to
     * clickable links to their documentation pages.
     *
     * @param $urlLinker The linker for generating relative URLs
     *
     * @return string|null Markdown-formatted type, or null if no type declared
     */
    public function getTypeMd(UrlLinker $urlLinker): ?string
    {
        return Util::getTypeMd($this->reflection->getType(), $urlLinker);
    }
}

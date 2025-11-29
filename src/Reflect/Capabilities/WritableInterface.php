<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Capabilities;

use JulienBoudry\PhpReference\UrlLinker;

/**
 * Interface for elements that can be written as documentation pages.
 *
 * This interface is implemented by wrappers that generate their own
 * documentation pages (classes, methods, properties, functions, namespaces).
 * It provides the information needed to:
 * - Determine where the page should be written
 * - Generate relative links to other pages
 *
 * @see UrlLinker For generating relative links between pages
 * @see AbstractWriter For writing page content
 */
interface WritableInterface
{
    /**
     * Returns the directory where this element's page resides.
     *
     * This is used as the base for calculating relative links to other pages.
     * For example: '/ref/MyApp/Domain/MyClass'
     */
    public function getPageDirectory(): string;

    /**
     * Returns the full path to this element's documentation page.
     *
     * This is the path relative to the output directory where the page
     * will be written. For example: '/ref/MyApp/Domain/MyClass/class_MyClass.md'
     */
    public function getPagePath(): string;

    /**
     * Returns a UrlLinker configured for generating links from this page.
     *
     * The returned UrlLinker calculates relative paths from this page
     * to any other page in the documentation.
     */
    public function getUrlLinker(): UrlLinker;
}

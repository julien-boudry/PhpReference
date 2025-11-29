<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;

/**
 * Represents the API summary page (readme.md at the root of documentation output).
 *
 * This class implements WritableInterface to enable URL linking from and to
 * the main summary page. It serves as a virtual page representation that allows
 * the UrlLinker to calculate relative paths from the root documentation page
 * to other documentation pages.
 *
 * @see WritableInterface Contract for pages that can be linked in documentation
 * @see UrlLinker For generating relative links between documentation pages
 */
class ApiSummaryPage implements WritableInterface
{
    /**
     * Cached UrlLinker instance for generating links from this page.
     */
    protected ?UrlLinker $urlLinker = null;

    /**
     * Creates a new API summary page instance.
     *
     * @param string $pagePath The path to the summary file relative to output root
     */
    public function __construct(
        public readonly string $pagePath = '/readme.md',
    ) {}

    /**
     * Returns the directory containing this page.
     *
     * For the API summary page, this is always the root directory.
     */
    public function getPageDirectory(): string
    {
        return '/';
    }

    /**
     * Returns the full path to this page file.
     */
    public function getPagePath(): string
    {
        return $this->pagePath;
    }

    /**
     * Returns a UrlLinker instance configured for this page.
     *
     * The UrlLinker is lazily instantiated and cached for reuse.
     */
    public function getUrlLinker(): UrlLinker
    {
        return $this->urlLinker ??= new UrlLinker($this);
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\UrlLinker;

/**
 * Wrapper for the API summary page (readme.md at root)
 * Implements WritableInterface to allow UrlLinker creation
 */
class ApiSummaryPage implements WritableInterface
{
    protected ?UrlLinker $urlLinker = null;

    public function __construct(
        public readonly string $pagePath = '/readme.md',
    ) {}

    public function getPageDirectory(): string
    {
        return '/';
    }

    public function getPagePath(): string
    {
        return $this->pagePath;
    }

    public function getUrlLinker(): UrlLinker
    {
        return $this->urlLinker ??= new UrlLinker($this);
    }
}

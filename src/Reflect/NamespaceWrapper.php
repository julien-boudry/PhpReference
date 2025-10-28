<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\UrlLinker;

class NamespaceWrapper implements WritableInterface
{
    public readonly UrlLinker $urlLinker;

    public protected(set) array $hierarchy;

    public string $shortName {
        get {
            $parts = explode('\\', $this->namespace);
            return end($parts) ?: $this->namespace;
        }
    }

    /**
     * @param array<string, ClassWrapper> $classes
     */
    public function __construct(
        public readonly string $namespace,
        public readonly array $classes,
    )
    {
        $this->urlLinker = new UrlLinker($this);
    }

    public function setHierarchy(array $hierarchy): void
    {
        $this->hierarchy ??= $hierarchy;
    }

    public function getPageDirectory(): string
    {
        return '/ref/' . str_replace('\\', '/', $this->namespace);
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . '/readme.md';
    }

    public function getUrlLinker(): UrlLinker
    {
        return $this->urlLinker;
    }
}

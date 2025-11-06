<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\UrlLinker;

class NamespaceWrapper implements WritableInterface
{
    public readonly UrlLinker $urlLinker;

    /** @var array<int, NamespaceWrapper|string> */
    public protected(set) array $hierarchy;

    public string $name {
        get => $this->namespace;
    }

    /** @var array<ClassWrapper|FunctionWrapper> */
    public array $elements {
        get => array_merge($this->classes, $this->functions);
    }

    /** @var array<ClassWrapper|FunctionWrapper> */
    public array $apiElements {
        get => array_merge($this->apiClasses, $this->apiFunctions);
    }

    /** @var array<ClassWrapper> */
    public array $apiClasses {
        get => array_filter(
            $this->classes,
            fn(ClassWrapper $class) => $class->willBeInPublicApi
        );
    }

    /** @var array<FunctionWrapper> */
    public array $apiFunctions {
        get => array_filter(
            $this->functions,
            fn(FunctionWrapper $function) => $function->willBeInPublicApi
        );
    }

    public string $shortName {
        get {
            $parts = explode('\\', $this->namespace);
            $lastPart = end($parts);

            return $lastPart ? $lastPart : $this->namespace;
        }
    }

    /**
     * @param array<string, ClassWrapper> $classes
     * @param array<string, FunctionWrapper> $functions
     */
    public function __construct(
        public readonly string $namespace,
        public readonly array $classes,
        public readonly array $functions,
    ) {
        $this->urlLinker = new UrlLinker($this);
    }

    /**
     * @param array<int, NamespaceWrapper|string> $hierarchy
     */
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

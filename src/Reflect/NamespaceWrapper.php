<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\UrlLinker;

/**
 * Groups classes and functions by namespace for documentation organization.
 *
 * The NamespaceWrapper represents a PHP namespace and provides access to all
 * classes and functions declared within it. It also maintains hierarchy
 * information for breadcrumb navigation.
 *
 * Each namespace gets its own documentation page that serves as an index
 * to the elements it contains.
 *
 * @see WritableInterface For page generation capabilities
 */
class NamespaceWrapper implements WritableInterface
{
    /**
     * Cached URL linker for this namespace.
     */
    public readonly UrlLinker $urlLinker;

    /**
     * The namespace hierarchy for breadcrumb navigation.
     *
     * Contains parent namespaces as either NamespaceWrapper (if indexed) or strings.
     *
     * @var array<int, NamespaceWrapper|string>
     */
    public protected(set) array $hierarchy;

    /**
     * The fully qualified namespace name.
     */
    public string $name {
        get => $this->namespace;
    }

    /**
     * All elements (classes and functions) in this namespace.
     *
     * @var array<ClassWrapper|FunctionWrapper>
     */
    public array $elements {
        get => array_merge($this->classes, $this->functions);
    }

    /**
     * API elements only (classes and functions in the public API).
     *
     * @var array<ClassWrapper|FunctionWrapper>
     */
    public array $apiElements {
        get => array_merge($this->apiClasses, $this->apiFunctions);
    }

    /**
     * Classes that are part of the public API.
     *
     * @var array<ClassWrapper>
     */
    public array $apiClasses {
        get => array_filter(
            $this->classes,
            fn(ClassWrapper $class) => $class->willBeInPublicApi
        );
    }

    /**
     * Functions that are part of the public API.
     *
     * @var array<FunctionWrapper>
     */
    public array $apiFunctions {
        get => array_filter(
            $this->functions,
            fn(FunctionWrapper $function) => $function->willBeInPublicApi
        );
    }

    /**
     * The short namespace name (last segment only).
     */
    public string $shortName {
        get {
            $parts = explode('\\', $this->namespace);
            $lastPart = end($parts);

            return $lastPart ? $lastPart : $this->namespace;
        }
    }

    /**
     * Creates a new namespace wrapper.
     *
     * @param $namespace The fully qualified namespace
     * @param $classes   Classes in this namespace
     * @param $functions Functions in this namespace
     */
    public function __construct(
        public readonly string $namespace,
        public readonly array $classes,
        public readonly array $functions,
    ) {
        $this->urlLinker = new UrlLinker($this);
    }

    /**
     * Sets the namespace hierarchy for breadcrumb navigation.
     *
     * Can only be set once (first call wins).
     *
     * @param $hierarchy Parent namespaces
     */
    public function setHierarchy(array $hierarchy): void
    {
        $this->hierarchy ??= $hierarchy;
    }

    /**
     * Returns the directory for this namespace's documentation page.
     */
    public function getPageDirectory(): string
    {
        return '/ref/' . str_replace('\\', '/', $this->namespace);
    }

    /**
     * Returns the full path for this namespace's documentation page.
     */
    public function getPagePath(): string
    {
        return $this->getPageDirectory() . '/readme.md';
    }

    /**
     * Returns a URL linker configured for this namespace.
     */
    public function getUrlLinker(): UrlLinker
    {
        return $this->urlLinker;
    }
}

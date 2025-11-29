<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, NamespaceWrapper, ReflectionWrapper};

/**
 * Provides navigation utilities for generating breadcrumb trails in documentation.
 *
 * This class generates Markdown-formatted breadcrumb navigation strings that show
 * the hierarchical path to an element. The breadcrumbs help users understand where
 * they are in the documentation and provide clickable links to parent elements.
 *
 * @see UrlLinker For generating the relative links used in breadcrumbs
 */
class Navigation
{
    /**
     * Generates a Markdown breadcrumb navigation string for an element.
     *
     * The breadcrumb shows the namespace hierarchy with links to documented namespaces,
     * and plain text for non-documented parent namespaces. For class elements, it also
     * includes a link to the parent class.
     *
     * Example output: "JulienBoudry \ [PhpReference](../readme.md) \ **MyClass**"
     *
     * @param ReflectionWrapper|NamespaceWrapper $element The element to generate breadcrumbs for
     *
     * @return string|null Markdown-formatted breadcrumb string, or null if unavailable
     */
    public static function getBreadcrumb(ReflectionWrapper|NamespaceWrapper $element): ?string
    {
        $urlLinker = $element->getUrlLinker();

        $path = '';

        // Handle namespace hierarchy
        $hierarchy = match (true) {
            $element instanceof NamespaceWrapper => $element->hierarchy,
            default => $element->declaringNamespace->hierarchy,
        };

        foreach ($hierarchy as $nsPart) {
            if (\is_string($nsPart)) {
                $parentNamespace = explode('\\', $nsPart);
                $path .= end($parentNamespace);
                unset($parentNamespace);
            } else {
                $path .= "[{$nsPart->shortName}]({$urlLinker->to($nsPart)})";
            }

            $path .= ' \\ ';
        }

        // Handle different element types
        if ($element instanceof ClassElementWrapper) {
            // For class elements (methods, properties), show the parent class as a link
            $parentWrapper = $element->parentWrapper;

            $className = $parentWrapper->shortName;
            $classLink = $urlLinker->to($parentWrapper);

            $path .= "[{$className}]({$classLink})";
        } else {
            // For classes themselves, show as bold
            $name = property_exists($element, 'shortName') ? $element->shortName : $element->name;
            $path .= "**{$name}**";
        }

        return $path;
    }
}

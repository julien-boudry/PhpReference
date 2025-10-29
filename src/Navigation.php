<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, NamespaceWrapper, ReflectionWrapper};

class Navigation
{
    /**
     * Get breadcrumb navigation data for an element or namespace.
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

        foreach ($hierarchy as $nsPart)
        {
            if (is_string($nsPart)) {
                $path .= $nsPart;
            }
            else {
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
        }
        else {
            // For classes themselves, show as bold
            $name = property_exists($element, 'shortName') ? $element->shortName : $element->name;
            $path .= "**{$name}**";
        }

        return $path;
    }
}

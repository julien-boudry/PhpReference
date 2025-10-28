<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ReflectionWrapper};
use JulienBoudry\PhpReference\UrlLinker;

abstract class AbstractElementInput
{
    protected readonly WritableInterface&ReflectionWrapper $reflectionWrapper;

    public string $pagePath {
        get => $this->reflectionWrapper->getPagePath();
    }

    public string $pageDirectory {
        get => $this->reflectionWrapper->getPageDirectory();
    }

    public UrlLinker $urlLinker {
        get => $this->reflectionWrapper->getUrlLinker();
    }

    /**
     * Get breadcrumb navigation data for a class element.
     */
    public static function getBreadcrumb(ReflectionWrapper $element): ?string
    {
        $urlLinker = $element->getUrlLinker();

        $path = '';

        foreach ($element->declaringNamespace->hierarchy as $nsPart)
        {
            if (is_string($nsPart)) {
                $path .= $nsPart;
            }
            else {
                $path .= "[{$nsPart->shortName}]({$urlLinker->to($nsPart)})";
            }

            $path .= ' \\ ';
        }

        if ($element instanceof ClassElementWrapper) {
            $parentWrapper = $element->parentWrapper;

            $className = $parentWrapper->shortName;
            $classLink = $urlLinker->to($parentWrapper);

            $path .= "[{$className}]({$classLink})";
        }
        else {
            $name = method_exists($element, 'shortName') ? $element->shortName : $element->name; // @phpstan-ignore property.notFound
            $path .= "**{$name}**";
        }

        return $path;
    }
}

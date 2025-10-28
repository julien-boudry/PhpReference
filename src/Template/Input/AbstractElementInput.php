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
    public static function getBreadcrumb(ClassElementWrapper $element): ?string
    {
        $parentWrapper = $element->parentWrapper;

        if (! $parentWrapper) {
            return null;
        }

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

        $className = $parentWrapper->shortName;
        $classLink = $urlLinker->to($parentWrapper);

        return $path . "[{$className}]({$classLink})";
    }
}

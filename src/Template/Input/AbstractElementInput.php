<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\Reflect\ClassElementWrapper;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
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
     * Get breadcrumb navigation data for a class element
     *
     * @return array{namespace: string, className: string, classUrl: string, elementName: string}|null
     */
    public static function getBreadcrumb(ClassElementWrapper $element): ?array
    {
        $parentWrapper = $element->parentWrapper;

        if (! $parentWrapper) {
            return null;
        }

        $namespaceParts = explode('\\', $parentWrapper->name);
        $className = array_pop($namespaceParts);
        $namespace = implode('\\', $namespaceParts);

        $urlLinker = $element->getUrlLinker();

        return [
            'namespace' => $namespace,
            'className' => $className,
            'classUrl' => $urlLinker->to($parentWrapper),
            'elementName' => $element->name,
        ];
    }
}

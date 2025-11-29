<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
use JulienBoudry\PhpReference\UrlLinker;

/**
 * Abstract base class for template input data objects.
 *
 * Template inputs serve as data transfer objects that prepare and organize
 * data for Latte templates. They provide a clean interface between the
 * reflection wrappers and the template system.
 *
 * All element inputs share common properties for page paths and URL linking.
 *
 * @see ClassPageInput For class page templates
 * @see MethodPageInput For method page templates
 * @see PropertyPageInput For property page templates
 * @see FunctionPageInput For function page templates
 */
abstract class AbstractElementInput
{
    /**
     * The underlying reflection wrapper that provides page information.
     */
    protected readonly WritableInterface&ReflectionWrapper $reflectionWrapper;

    /**
     * The full path to the documentation page.
     */
    public string $pagePath {
        get => $this->reflectionWrapper->getPagePath();
    }

    /**
     * The directory containing the documentation page.
     */
    public string $pageDirectory {
        get => $this->reflectionWrapper->getPageDirectory();
    }

    /**
     * The URL linker for generating relative links from this page.
     */
    public UrlLinker $urlLinker {
        get => $this->reflectionWrapper->getUrlLinker();
    }
}

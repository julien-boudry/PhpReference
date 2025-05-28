<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\UrlLinker;

abstract class AbstractElementInput
{
    protected readonly WritableInterface & ReflectionWrapper $reflectionWrapper;

    public string $pagePath {
        get => $this->reflectionWrapper->getPagePath();
    }

    public string $pageDirectory {
        get => $this->reflectionWrapper->getPageDirectory();
    }

    public UrlLinker $urlLinker {
        get => $this->reflectionWrapper->getUrlLinker();
    }
}

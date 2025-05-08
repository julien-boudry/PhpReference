<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Formater;

use JulienBoudry\PhpReference\Reflect\ClassConstantWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Reflect\PropertyWrapper;

class ClassFormater extends ClassFormater
{
    /** @var array<string, ClassConstantWrapper> */
    public array $constEntries {
        get => $this->class->getAllApiConstants();
    }

    /** @var array<string, PropertyWrapper> */
    public array $staticPropertiesEntries {
        get => $this->class->getAllApiProperties(static: true, nonStatic: false);
    }

    /** @var array<string, MethodWrapper> */
    public array $staticMethodsEntries {
        get => $this->class->getAllApiMethods(static: true, nonStatic: false);
    }

    /** @var array<string, PropertyWrapper> */
    public array $PropertiesEntries {
        get => $this->class->getAllApiProperties(static: false);
    }

    /** @var array<string, MethodWrapper> */
    public array $MethodsEntries {
        get => $this->class->getAllApiMethods(static: false);
    }
}
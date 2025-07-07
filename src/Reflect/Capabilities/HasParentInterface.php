<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Capabilities;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

interface HasParentInterface
{
    public ?ReflectionWrapper $parentWrapper {
        get;
    }
}

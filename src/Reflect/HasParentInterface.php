<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

interface HasParentInterface
{
    public ?ReflectionWrapper $parentWrapper {
        get;
    }
}
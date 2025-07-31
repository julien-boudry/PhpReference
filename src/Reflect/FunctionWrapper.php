<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, IsFunction};
use ReflectionFunction;

class FunctionWrapper extends ReflectionWrapper
{
    use CanThrow;
    use IsFunction;

    public ReflectionFunction $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }
}

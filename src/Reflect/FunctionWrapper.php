<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, IsFunction};
use Roave\BetterReflection\Reflection\ReflectionFunction;

class FunctionWrapper extends ReflectionWrapper implements SignatureInterface, WritableInterface
{
    use CanThrow;
    use IsFunction;

    public ReflectionFunction $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/function_{$this->name}.md";
    }

    public function getSignature(bool $withClassName = false): string
    {
        return 'function ' . $this->getFunctionPartSignature();
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Structure\{CanThrow, IsFunction};
use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use ReflectionMethod;

class MethodWrapper extends ClassElementWrapper implements SignatureInterface, WritableInterface
{
    use CanThrow;
    use IsFunction;

    public ReflectionMethod $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/method_{$this->name}.md";
    }

    public function getSignature(bool $withClassName = false): string
    {
        $str = '(';

        if ($this->reflection->getNumberOfParameters() > 0) {
            $option = false;
            $i = 0;

            foreach ($this->getParameters() as $param) {
                $str .= $i === 0 ? ' ' : ', ';
                $str .= ($param->reflection->isOptional() && ! $option) ? '[ ' : '';

                $str .= $param->getSignature();

                ($param->reflection->isOptional() && ! $option) ? $option = true : null;
                $i++;
            }

            if ($option) {
                $str .= ' ]';
            }
        }

        $str .= ' )';

        return $this->getModifierNames() .
                ' function ' .
                (! $withClassName ? $this->inDocParentWrapper->shortName : '') .
                (! $withClassName ? ($this->reflection->isStatic() ? '::' : '->') : '') .
                $this->reflection->name .
                $str .
                ($this->hasReturnType() ? ': ' . $this->getReturnType() : '');
    }
}

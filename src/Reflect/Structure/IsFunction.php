<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Reflect\ParameterWrapper;
use JulienBoudry\PhpReference\{UrlLinker, Util};
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait IsFunction
{
    public function isUserDefined(): bool
    {
        return $this->reflection->isUserDefined();
    }

    /**
     * @return array<int, ParameterWrapper>
     */
    public function getParameters(): array
    {
        return array_map(
            function (ReflectionParameter $parameter): ParameterWrapper {
                return new ParameterWrapper($parameter, $this);
            },
            $this->reflection->getParameters()
        );
    }

    public function hasReturnType(): bool
    {
        return $this->reflection->hasReturnType();
    }

    public function getReturnType(): string
    {
        if (! $this->hasReturnType()) {
            throw new \RuntimeException(
                'Method ' . $this->reflection->getName() . ' has no return type.'
            );
        }

        return (string) $this->reflection->getReturnType();
    }

    public function getReturnTypeMd(UrlLinker $urlLinker): string
    {
        $type = $this->reflection->getReturnType();

        return Util::getTypeMd($type, $urlLinker);
    }

    public function getReturnDescription(): ?string
    {
        return $this->getDocBlockTagDescription('return');
    }

    protected function getFunctionPartSignature(): string
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

        return $this->reflection->getName()
                . $str
                . ($this->hasReturnType() ? ': ' . $this->getReturnType() : '');
    }
}

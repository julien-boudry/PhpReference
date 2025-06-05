<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Reflect\ParameterWrapper;
use ReflectionParameter;

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
     *
     * @return array<ParameterWrapper>
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
        if (!$this->hasReturnType()) {
            throw new \RuntimeException(
                'Method ' . $this->reflection->getName() . ' has no return type.'
            );
        }

        return (string) $this->reflection->getReturnType();
    }

    public function getReturnDescription(): ?string
    {
        return $this->getDocBlockTagDescription('return');
    }
}
<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait HasReturn
{
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
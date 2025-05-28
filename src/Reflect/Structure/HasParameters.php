<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Reflect\ParameterWrapper;
use ReflectionParameter;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 */
trait HasParameters
{
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
}
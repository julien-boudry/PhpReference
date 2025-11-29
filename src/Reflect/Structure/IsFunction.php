<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Reflect\ParameterWrapper;
use JulienBoudry\PhpReference\{UrlLinker, Util};
use ReflectionParameter;

/**
 * Trait providing common functionality for function-like elements.
 *
 * This trait is used by MethodWrapper and FunctionWrapper to provide
 * shared functionality for elements that have parameters and return types.
 * It includes methods for:
 * - Checking if the element is user-defined
 * - Getting wrapped parameters
 * - Accessing return type information
 * - Generating the function signature portion
 *
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait IsFunction
{
    /**
     * Checks if this function/method is user-defined (not built-in).
     */
    public function isUserDefined(): bool
    {
        return $this->reflection->isUserDefined();
    }

    /**
     * Returns wrapped parameters for this function/method.
     *
     * @return array<int, ParameterWrapper> Array of parameter wrappers in declaration order
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

    /**
     * Checks if this function/method has a declared return type.
     */
    public function hasReturnType(): bool
    {
        return $this->reflection->hasReturnType();
    }

    /**
     * Returns the declared return type as a string.
     *
     * @throws \RuntimeException If no return type is declared
     */
    public function getReturnType(): string
    {
        if (! $this->hasReturnType()) {
            throw new \RuntimeException(
                'Method ' . $this->reflection->getName() . ' has no return type.'
            );
        }

        return (string) $this->reflection->getReturnType();
    }

    /**
     * Returns the return type as Markdown with automatic cross-linking.
     *
     * @param $urlLinker The linker for generating relative URLs
     */
    public function getReturnTypeMd(UrlLinker $urlLinker): string
    {
        $type = $this->reflection->getReturnType();

        return Util::getTypeMd($type, $urlLinker);
    }

    /**
     * Returns the description for the return value from @return tag.
     *
     * @return The return description, or null if not documented
     */
    public function getReturnDescription(): ?string
    {
        return $this->getDocBlockTagDescription('return');
    }

    /**
     * Generates the function/method signature portion (name, params, return).
     *
     * This generates the part of the signature after modifiers, in the format:
     * `methodName( param1, param2 ): ReturnType`
     *
     * Optional parameters are wrapped in square brackets.
     */
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

        return $this->reflection->name
                . $str
                . ($this->hasReturnType() ? ': ' . $this->getReturnType() : '');
    }
}

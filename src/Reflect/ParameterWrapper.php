<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\{HasParentInterface, SignatureInterface};
use JulienBoudry\PhpReference\Reflect\Structure\HasType;
use ReflectionParameter;
use WeakReference;

/**
 * Wrapper for PHP function/method parameter reflection.
 *
 * Provides access to parameter type, default value, pass-by-reference status,
 * and documentation from the parent function's @param tags.
 *
 * Uses WeakReference for parent function references to avoid circular reference
 * memory issues.
 *
 * @see HasParentInterface For parent access
 * @see HasType For type information handling
 */
class ParameterWrapper extends ReflectionWrapper implements HasParentInterface, SignatureInterface
{
    use HasType;

    /**
     * The underlying ReflectionParameter.
     */
    public ReflectionParameter $reflection {
        get => $this->reflector; // @phpstan-ignore return.type
    }

    /**
     * Weak reference to the parent function or method.
     *
     * @var WeakReference<MethodWrapper|FunctionWrapper>
     */
    protected WeakReference $parentFunctionReference;

    /**
     * The parent function or method wrapper.
     */
    public MethodWrapper|FunctionWrapper|null $parentWrapper {
        get => $this->parentFunctionReference->get();
    }

    /**
     * Creates a new parameter wrapper.
     *
     * @param $reflectionParameter The PHP reflection parameter
     * @param $functionWrapper     The parent function wrapper
     */
    public function __construct(ReflectionParameter $reflectionParameter, MethodWrapper|FunctionWrapper $functionWrapper)
    {
        parent::__construct($reflectionParameter);

        $this->parentFunctionReference = WeakReference::create($functionWrapper);
    }

    /**
     * Returns the description from the @param tag for this parameter.
     */
    public function getDescription(): ?string
    {
        return $this->parentWrapper->getDocBlockTagDescription('param', $this->name);
    }

    /**
     * Generates the parameter signature for documentation.
     *
     * Includes type, pass-by-reference indicator, name, and default value.
     */
    public function getSignature(): string
    {
        $refl = $this->reflection;

        $str = (string) $refl->getType();
        $str .= ' ';
        $str .= $refl->isPassedByReference() ? '&' : '';
        $str .= '$' . $this->name;
        $str .= $refl->isDefaultValueAvailable() ? ' = ' . self::formatValue($refl->getDefaultValue()) : '';

        return $str;
    }
}

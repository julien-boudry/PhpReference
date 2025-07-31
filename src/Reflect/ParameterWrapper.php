<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\{HasParentInterface, SignatureInterface};
use JulienBoudry\PhpReference\Reflect\Structure\HasType;
use ReflectionParameter;
use WeakReference;

class ParameterWrapper extends ReflectionWrapper implements HasParentInterface, SignatureInterface
{
    use HasType;

    public ReflectionParameter $reflection {
        get => $this->reflector; // @phpstan-ignore return.type
    }

    /** @var WeakReference<MethodWrapper|FunctionWrapper> */
    protected WeakReference $parentFunctionReference;

    public MethodWrapper|FunctionWrapper|null $parentWrapper {
        get => $this->parentFunctionReference->get();
    }

    public function __construct(ReflectionParameter $reflectionParameter, MethodWrapper|FunctionWrapper $functionWrapper)
    {
        parent::__construct($reflectionParameter);

        $this->parentFunctionReference = WeakReference::create($functionWrapper);
    }

    public function getDescription(): ?string
    {
        return $this->parentWrapper->getDocBlockTagDescription('param', $this->name);
    }

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

<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Structure\HasType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperties;
use ReflectionProperty;
use WeakReference;

class ParameterWrapper extends ReflectionWrapper implements SignatureInterface
{
    use HasType;

    public ReflectionParameter $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /** @var WeakReference<MethodWrapper|FunctionWrapper> */
    protected WeakReference $parentFunctionReference;

    public MethodWrapper|FunctionWrapper|null $parentFunctionWrapper {
        get => $this->parentFunctionReference->get();
    }

    public function __construct(ReflectionParameter $reflectionParameter, MethodWrapper|FunctionWrapper $functionWrapper)
    {
        parent::__construct($reflectionParameter);

        $this->parentFunctionReference = WeakReference::create($functionWrapper);
    }

    public function getDescription(): ?string
    {
        return $this->parentFunctionWrapper->getDocBlockTagDescription('param', $this->name);
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
<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Structure\CanThrow;
use JulienBoudry\PhpReference\Reflect\Structure\HasReturn;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperties;
use ReflectionProperty;

class MethodWrapper extends ClassElementWrapper implements WritableInterface, SignatureInterface
{
    use HasReturn;
    use CanThrow;

    public ReflectionMethod $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(
        ReflectionMethod $reflectionMethod,
        ClassWrapper $classWrapper
    )
    {
        parent::__construct($reflectionMethod, $classWrapper);
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/method_{$this->name}.md";
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

    public function getSignature(bool $forClassRepresentation = false): string
    {
        $str = '(';

        if ($this->reflection->getNumberOfParameters() > 0) {
            $option = false;
            $i = 0;

            foreach ($this->getParameters() as $param) {
                $str .= $i === 0 ? ' ' : ', ';
                $str .= ($param->reflection->isOptional() && !$option) ? '[ ' : '';

                $str .= $param->getSignature();

                ($param->reflection->isOptional() && !$option) ? $option = true : null;
                $i++;
            }

            if ($option) {
                $str .= ' ]';
            }
        }

            $str .= ' )';

            $str = $this->getModifierNames() .
                    ' function ' .
                    (!$forClassRepresentation ? $this->classWrapper->name : '') .
                    (!$forClassRepresentation ? ($this->reflection->isStatic() ? '::' : '->') : '').
                    $this->reflection->name .
                    $str .
                    ($this->hasReturnType() ? ': ' . $this->getReturnType() : '')
            ;

            return $str;
    }
}
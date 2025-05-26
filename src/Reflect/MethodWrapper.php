<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
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

    public function getSignature(): string
    {
        $str = '(';

        if ($this->reflection->getNumberOfParameters() > 0) {
            $option = false;
            $i = 0;

            foreach ($this->reflection->getParameters() as $value) {
                $str .= ' ';
                $str .= ($value->isOptional() && !$option) ? '[' : '';
                $str .= ($i > 0) ? ', ' : '';
                $str .= (string) $value->getType();
                $str .= ' ';
                $str .= $value->isPassedByReference() ? '&' : '';
                $str .= '$' . $value->getName();
                $str .= $value->isDefaultValueAvailable() ? ' = ' . self::formatValue($value->getDefaultValue()) : '';

                ($value->isOptional() && !$option) ? $option = true : null;
                $i++;
            }

            if ($option) {
                $str .= ']';
            }
        }

            $str .= ' )';

            $returnType = (string) $this->reflection->getReturnType();

            $str = $this->getModifierNames() .
                    ' ' .
                    $this->classWrapper->name .
                    ($this->reflection->isStatic() ? '::' : '->') .
                    $this->reflection->name .
                    $str .
                    ($this->reflection->hasReturnType() ? ': ' . $returnType : '')
            ;

            return $str;
    }
}
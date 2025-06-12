<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use JulienBoudry\PhpReference\Reflect\Structure\CanThrow;
use JulienBoudry\PhpReference\Reflect\Structure\IsFunction;
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
    use IsFunction;
    use CanThrow;

    public ReflectionMethod $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/method_{$this->name}.md";
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
                    (!$forClassRepresentation ? $this->parentWrapper->name : '') .
                    (!$forClassRepresentation ? ($this->reflection->isStatic() ? '::' : '->') : '').
                    $this->reflection->name .
                    $str .
                    ($this->hasReturnType() ? ': ' . $this->getReturnType() : '')
            ;

            return $str;
    }
}
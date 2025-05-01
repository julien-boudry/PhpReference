<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

abstract class ReflectionWrapper
{
    /**
     * @param array<ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionConstant|ReflectionFunction> $reflectors
     * @return array<ReflectionWrapper>
     */
    public static function toWrapper(array $reflectors, ClassWrapper $classWrapper): array
    {
        $wrappers = [];

        foreach ($reflectors as $reflector) {
            if ($reflector instanceof ReflectionMethod) {
                $wrappers[] = new MethodWrapper($reflector, $classWrapper);
            } elseif ($reflector instanceof ReflectionProperty) {
                $wrappers[] = new PropertyWrapper($reflector, $classWrapper);
            } elseif ($reflector instanceof ReflectionFunction) {
                $wrappers[] = new FunctionWrapper($reflector);
            }
        }

        return $wrappers;
    }


    public readonly ?DocBlock $docBlock;

    public readonly bool $hasApiTag;
    public readonly bool $hasInternalTag;

    protected function __construct(public readonly ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionFunction $reflection)
    {
         // Docblock
         $docComment = $this->reflection->getDocComment();
         $this->docBlock = !empty($docComment) ? Util::getDocBlocFactory()->create($docComment) : null;

        // DocBlock visibility
        if ($this->docBlock !== null && $this->docBlock->hasTag('api')) {
            $this->hasApiTag = true;
        } else {
            $this->hasApiTag = false;
        }

        if ($this->docBlock !== null && $this->docBlock->hasTag('internal')) {
            $this->hasInternalTag = true;
        } else {
            $this->hasInternalTag = false;
        }
    }
}
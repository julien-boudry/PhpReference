<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Util;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionFunctionAbstract;
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
            $wrappers[$reflector->getName()] = match (true) {
                $reflector instanceof ReflectionMethod => new MethodWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionProperty => new PropertyWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionClassConstant => new ClassConstantWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionFunction => new FunctionWrapper($reflector),
                default => throw new \LogicException('Unsupported reflector type: ' . get_class($reflector)),
            };
        }

        return $wrappers;
    }


    public readonly ?DocBlock $docBlock;

    public readonly bool $hasApiTag;
    public readonly bool $hasInternalTag;

    public function __construct(public readonly ReflectionClass|ReflectionProperty|ReflectionFunctionAbstract|ReflectionClassConstant $reflection)
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

    public ?string $name {
        get => $this->reflection->name ?? null;
    }

    public function getDescription(): ?string
    {
        return $this->docBlock?->getDescription()->render();
    }
}
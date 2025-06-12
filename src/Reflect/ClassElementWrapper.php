<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Capabilities\HasParentInterface;
use JulienBoudry\PhpReference\Util;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;
use WeakReference;

abstract class ClassElementWrapper extends ReflectionWrapper implements HasParentInterface
{
    /** @var WeakReference<ClassWrapper> */
    public readonly WeakReference $classReference;

    /** @var ?WeakReference<ClassWrapper> */
    public readonly ?WeakReference $declaringClassReference;

    public ?ClassWrapper $parentWrapper {
        get => $this->classReference->get();
    }

    public ?ClassWrapper $declaringClass {
        get => $this->declaringClassReference ? $this->declaringClassReference->get() : null;
    }

    public ?ClassWrapper $inDocParentWrapper {
        get {
            if ($this->declaringClass === null || !$this->declaringClass->willBeInPublicApi) {
                return $this->parentWrapper;
            }

            return $this->declaringClass;
        }
    }

    public ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflection {
        get => $this->reflector; // @phpstan-ignore return.type
    }

    public function __construct(
        ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflectorInClass,
        ClassWrapper $classWrapper,
        ?ClassWrapper $declaringClass
    )
    {
        $this->classReference = WeakReference::create($classWrapper);

        $this->declaringClassReference = $declaringClass ? WeakReference::create($declaringClass) : null;

        parent::__construct($reflectorInClass);
    }

    public function getPageDirectory(): string
    {
        return $this->inDocParentWrapper->getPageDirectory();
    }

    public function isPublic(): bool {
        return $this->reflection->isPublic();
    }

    public function isLocalTo(ClassWrapper $classWrapper): bool
    {
        return $this->reflection->getDeclaringClass()->name === $classWrapper->name;
    }
}
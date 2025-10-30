<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\HasParentInterface;
use Roave\BetterReflection\Reflection\ReflectionClassConstant;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
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

    public NamespaceWrapper $declaringNamespace {
        get => $this->parentWrapper->declaringNamespace; // @phpstan-ignore propertyGetHook.noRead
    }

    public ?ClassWrapper $declaringClass {
        get => $this->declaringClassReference ? $this->declaringClassReference->get() : null;
    }

    public ?ClassWrapper $inDocParentWrapper {
        get {
            if ($this->declaringClass === null || ! $this->declaringClass->willBeInPublicApi) {
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
    ) {
        $this->classReference = WeakReference::create($classWrapper);

        $this->declaringClassReference = $declaringClass ? WeakReference::create($declaringClass) : null;

        $this->docBlockContext = $classWrapper->docBlockContext;

        parent::__construct($reflectorInClass);
    }

    public function getPageDirectory(): string
    {
        return $this->inDocParentWrapper->getPageDirectory();
    }

    public function isPublic(): bool
    {
        return $this->reflection->isPublic();
    }

    public function isLocalTo(ClassWrapper $classWrapper): bool
    {
        return $this->reflection->getDeclaringClass()->getName() === $classWrapper->name;
    }
}

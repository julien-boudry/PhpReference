<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\HasParentInterface;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use WeakReference;

/**
 * Abstract base class for class member wrappers (methods, properties, constants).
 *
 * This class provides common functionality for all elements that belong to
 * a class, including parent class tracking, namespace resolution, and
 * visibility checking.
 *
 * Uses WeakReference for parent class references to avoid circular reference
 * memory issues.
 *
 * @see MethodWrapper For method-specific functionality
 * @see PropertyWrapper For property-specific functionality
 * @see ClassConstantWrapper For constant-specific functionality
 */
abstract class ClassElementWrapper extends ReflectionWrapper implements HasParentInterface
{
    /**
     * Weak reference to the containing class wrapper.
     *
     * @var WeakReference<ClassWrapper>
     */
    public readonly WeakReference $classReference;

    /**
     * Weak reference to the class where this element is declared (may differ from containing class for inherited elements).
     *
     * @var WeakReference<ClassWrapper>|null
     */
    public readonly ?WeakReference $declaringClassReference;

    /**
     * The parent class wrapper (the class containing this element).
     */
    public ?ClassWrapper $parentWrapper {
        get => $this->classReference->get();
    }

    /**
     * The namespace wrapper for the parent class.
     */
    public NamespaceWrapper $declaringNamespace {
        get => $this->parentWrapper->declaringNamespace; // @phpstan-ignore propertyGetHook.noRead
    }

    /**
     * The class where this element is declared (for inherited elements, differs from parent).
     */
    public ?ClassWrapper $declaringClass {
        get => $this->declaringClassReference ? $this->declaringClassReference->get() : null;
    }

    /**
     * The most appropriate parent to use in documentation (uses declaring class if documented).
     *
     * Returns the declaring class if it's part of the public API, otherwise returns
     * the containing class. This ensures inherited elements link to the most relevant
     * documentation.
     */
    public ?ClassWrapper $inDocParentWrapper {
        get {
            if ($this->declaringClass === null || ! $this->declaringClass->willBeInPublicApi) {
                return $this->parentWrapper;
            }

            return $this->declaringClass;
        }
    }

    /**
     * The underlying PHP reflector.
     */
    public ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflection {
        get => $this->reflector; // @phpstan-ignore return.type
    }

    /**
     * Creates a new class element wrapper.
     *
     * @param $reflectorInClass The PHP reflector
     * @param $classWrapper     The containing class
     * @param $declaringClass   The declaring class (for inherited elements)
     */
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

    /**
     * Returns the page directory (same as parent class).
     */
    public function getPageDirectory(): string
    {
        return $this->inDocParentWrapper->getPageDirectory();
    }

    /**
     * Checks if this element has public visibility.
     */
    public function isPublic(): bool
    {
        return $this->reflection->isPublic();
    }

    /**
     * Checks if this element is declared in the specified class (not inherited).
     */
    public function isLocalTo(ClassWrapper $classWrapper): bool
    {
        return $this->reflection->getDeclaringClass()->name === $classWrapper->name;
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\HasParentInterface;
use JulienBoudry\PhpReference\Util;
use phpDocumentor\Reflection\Types\Context;
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
     * Alternative DocBlock context from the trait/parent file where this method is defined.
     * Used as fallback when resolving type references that can't be found with the main context.
     */
    public readonly ?Context $alternativeDocBlockContext;

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
     * @param ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflectorInClass The PHP reflector
     * @param ClassWrapper                                                  $classWrapper     The containing class
     * @param ClassWrapper|null                                             $declaringClass   The declaring class (for inherited elements)
     */
    public function __construct(
        ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflectorInClass,
        ClassWrapper $classWrapper,
        ?ClassWrapper $declaringClass
    ) {
        $this->classReference = WeakReference::create($classWrapper);

        $this->declaringClassReference = $declaringClass ? WeakReference::create($declaringClass) : null;

        // For methods defined in traits or parent classes, we need both contexts:
        // - The main context from the containing class (for @see references to sibling methods)
        // - An alternative context from the trait/parent file (for @throws with locally imported exceptions)
        //
        // phpDocumentor's ContextFactory::createFromReflector() uses getDeclaringClass()
        // which returns the class using the trait, not the trait itself.
        if ($reflectorInClass instanceof ReflectionMethod) {
            $methodFile = $reflectorInClass->getFileName();
            $classFile = $classWrapper->reflection->getFileName();

            // Always use the class context as main (preserves @see behavior)
            $this->docBlockContext = $classWrapper->docBlockContext;

            // If the method is defined in a different file (trait or parent class),
            // create an alternative context from that file for fallback resolution
            if ($methodFile !== false && $classFile !== false && $methodFile !== $classFile) {
                $this->alternativeDocBlockContext = $this->createContextFromFile($methodFile);
            } else {
                $this->alternativeDocBlockContext = null;
            }
        } else {
            // For properties and constants, use the class context only
            $this->docBlockContext = $classWrapper->docBlockContext;
            $this->alternativeDocBlockContext = null;
        }

        parent::__construct($reflectorInClass);
    }

    /**
     * Creates a DocBlock context from a PHP file by parsing its namespace and use statements.
     *
     * @param string $filePath Path to the PHP file
     *
     * @return \phpDocumentor\Reflection\Types\Context
     */
    private function createContextFromFile(string $filePath): \phpDocumentor\Reflection\Types\Context
    {
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            // Fallback to parent class context
            return $this->classReference->get()->docBlockContext;
        }

        $namespace = $this->extractNamespaceFromFile($fileContents);

        return Util::getDocBlocContextFactory()->createForNamespace($namespace, $fileContents);
    }

    /**
     * Extracts the namespace declaration from PHP file contents.
     *
     * @param string $fileContents The contents of the PHP file
     *
     * @return string The namespace, or empty string if no namespace
     */
    private function extractNamespaceFromFile(string $fileContents): string
    {
        $tokens = token_get_all($fileContents);

        foreach ($tokens as $i => $token) {
            if (\is_array($token) && $token[0] === T_NAMESPACE) {
                // Find the namespace name after T_NAMESPACE
                $namespace = '';
                for ($j = $i + 1; $j < \count($tokens); $j++) {
                    $nextToken = $tokens[$j];
                    if (\is_array($nextToken)) {
                        if (\in_array($nextToken[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED], true)) {
                            $namespace .= $nextToken[1];
                        } elseif ($nextToken[0] !== T_WHITESPACE) {
                            break;
                        }
                    } elseif ($nextToken === ';' || $nextToken === '{') {
                        break;
                    }
                }

                return trim($namespace, '\\');
            }
        }

        return '';
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

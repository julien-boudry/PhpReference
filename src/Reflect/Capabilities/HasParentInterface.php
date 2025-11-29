<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Capabilities;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

/**
 * Interface for wrappers that have a parent element.
 *
 * This interface is implemented by wrappers that belong to another element,
 * enabling traversal up the documentation hierarchy. For example:
 * - MethodWrapper has a parent ClassWrapper
 * - PropertyWrapper has a parent ClassWrapper
 * - ParameterWrapper has a parent MethodWrapper or FunctionWrapper
 *
 * The parent relationship is maintained using WeakReference to avoid
 * circular reference memory issues.
 *
 * @see ClassElementWrapper For class member implementations
 * @see ParameterWrapper For parameter implementation
 */
interface HasParentInterface
{
    /**
     * Returns the parent wrapper element, or null if not available.
     *
     * The return type varies by implementation (ClassWrapper, MethodWrapper, etc.)
     */
    public ?ReflectionWrapper $parentWrapper {
        get;
    }
}

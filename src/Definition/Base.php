<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, FunctionWrapper, MethodWrapper, ReflectionWrapper};

/**
 * Abstract base class providing common exclusion logic for API definitions.
 *
 * This class implements the shared filtering logic that all API definitions use
 * to exclude elements that should never be part of the public API, regardless
 * of the specific inclusion strategy:
 *
 * - Elements marked with @internal are excluded
 * - Non-user-defined elements (PHP built-ins) are excluded
 * - Class elements whose parent class is marked @internal are excluded
 * - Class elements inherited from external namespaces are excluded
 *
 * Concrete implementations should call baseExclusion() first, then apply their
 * specific inclusion criteria.
 *
 * @see PublicApiDefinitionInterface The interface this class helps implement
 */
abstract class Base
{
    /**
     * Applies base exclusion rules that are common to all API definitions.
     *
     * This method checks for conditions that should exclude an element from
     * any public API, regardless of the specific definition strategy:
     *
     * 1. Elements with @internal tag are excluded
     * 2. Non-user-defined classes/methods/functions are excluded
     * 3. Class elements whose parent has @internal tag are excluded
     * 4. Class elements inherited from external namespaces are excluded
     *
     * @param ReflectionWrapper $reflectionWrapper The element to check
     *
     * @return bool True if the element passes base exclusion (not excluded),
     *              false if it should be excluded
     */
    protected function baseExclusion(ReflectionWrapper $reflectionWrapper): bool
    {
        if ($reflectionWrapper->hasInternalTag) {
            return false;
        }

        if ($reflectionWrapper instanceof ClassWrapper || $reflectionWrapper instanceof MethodWrapper || $reflectionWrapper instanceof FunctionWrapper) {
            if (! $reflectionWrapper->isUserDefined()) {
                return false;
            }
        }

        if ($reflectionWrapper instanceof ClassElementWrapper && $reflectionWrapper->parentWrapper->hasInternalTag) {
            return false;
        }

        // Exclude class elements inherited from external namespaces (declaring class not in the configured namespace)
        // These elements can be mentioned in class documentation but should not generate their own pages
        // declaringClass is null when the declaring class is not in our CodeIndex (i.e., external)
        // BUT we should not exclude elements that are local to their parent (declared in the same class)
        if ($reflectionWrapper instanceof ClassElementWrapper && $reflectionWrapper->declaringClass === null) {
            // Check if the element is local to its parent class (not inherited)
            // If local, keep it even if declaringClass is null (parent might not be in CodeIndex)
            if (! $reflectionWrapper->isLocalTo($reflectionWrapper->parentWrapper)) {
                return false;
            }
        }

        return true;
    }
}

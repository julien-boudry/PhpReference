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
     *
     * @param $reflectionWrapper The element to check
     *
     * @return True if the element passes base exclusion (not excluded),
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

        return true;
    }
}

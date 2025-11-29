<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

/**
 * Contract for defining what elements are part of the public API.
 *
 * Implementations of this interface determine which classes, methods, properties,
 * and other elements should be included in the generated documentation. This allows
 * for flexible API documentation strategies:
 *
 * - Include all public elements (IsPubliclyAccessible)
 * - Only include explicitly tagged elements (HasTagApi)
 * - Custom strategies based on naming conventions, annotations, or other criteria
 *
 * @see IsPubliclyAccessible Includes all public elements
 * @see HasTagApi Requires explicit @api tags
 * @see Base Abstract base providing common exclusion logic
 */
interface PublicApiDefinitionInterface
{
    /**
     * Determines whether an element should be included in the public API documentation.
     *
     * @param $reflectionWrapper The wrapped reflection element to check
     *
     * @return bool True if the element should be documented, false to exclude it
     */
    public function isPartOfPublicApi(ReflectionWrapper $reflectionWrapper): bool;
}

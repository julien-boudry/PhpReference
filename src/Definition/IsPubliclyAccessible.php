<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ReflectionWrapper};

/**
 * API definition that includes all publicly accessible elements.
 *
 * This is the most permissive API definition strategy. All elements with
 * public visibility are included in the documentation, making it suitable
 * for generating complete API references without requiring explicit @api tags.
 *
 * Inclusion rules:
 * - All classes (except those marked @internal or non-user-defined) are included
 * - All public methods, properties, and constants are included
 * - All functions are included
 * - Elements marked @internal are excluded via base exclusion rules
 *
 * This is the default API definition if none is specified in configuration.
 *
 * @see Base For the base exclusion rules applied before visibility check
 * @see HasTagApi For a stricter alternative requiring explicit @api tags
 */
class IsPubliclyAccessible extends Base implements PublicApiDefinitionInterface
{
    /**
     * Determines if an element is part of the public API based on visibility.
     *
     * @param ReflectionWrapper $reflectionWrapper The element to check
     *
     * @return bool True if the element is public (or is a class/function)
     */
    public function isPartOfPublicApi(ReflectionWrapper $reflectionWrapper): bool
    {
        if (! $this->baseExclusion($reflectionWrapper)) {
            return false;
        }

        if ($reflectionWrapper instanceof ClassElementWrapper) {
            if (! $reflectionWrapper->isPublic()) {
                return false;
            }

            return true;
        }

        return true;
    }
}

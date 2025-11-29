<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Definition;

use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, ReflectionWrapper};

/**
 * API definition that requires explicit @api PHPDoc tags for inclusion.
 *
 * This is the strictest API definition strategy. Elements are only included
 * in the documentation if they have an explicit @api tag in their PHPDoc
 * comment. This approach is ideal for libraries that want precise control
 * over their documented public API.
 *
 * Inclusion rules:
 * - Classes are included if they have @api on any public member, or on the class itself
 * - Class elements (methods, properties, constants) must have @api and be public
 * - Functions must have @api
 * - All elements must pass base exclusion rules (no @internal, user-defined only)
 *
 * @see Base For the base exclusion rules applied before checking @api
 * @see IsPubliclyAccessible For a more permissive alternative
 */
class HasTagApi extends Base implements PublicApiDefinitionInterface
{
    /**
     * Determines if an element is part of the public API based on @api tag presence.
     *
     * @param $reflectionWrapper The element to check
     *
     * @return True if the element has @api tag and meets other criteria
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
        }

        if ($reflectionWrapper instanceof ClassWrapper) {
            if (! empty($reflectionWrapper->getAllApiConstants()) || ! empty($reflectionWrapper->getAllApiProperties()) || ! empty($reflectionWrapper->getAllApiMethods())) {
                return true;
            }
        }

        return $reflectionWrapper->hasApiTag;
    }
}

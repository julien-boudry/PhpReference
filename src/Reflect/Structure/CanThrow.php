<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Reflect\ClassElementWrapper;
use JulienBoudry\PhpReference\Util;
use LogicException;
use phpDocumentor\Reflection\DocBlock;

/**
 * Trait for elements that can throw exceptions.
 *
 * This trait is used by MethodWrapper and FunctionWrapper to provide
 * functionality for parsing and resolving @throws tags from PHPDoc comments.
 *
 * The trait provides methods to:
 * - Get raw @throws tags from the docblock
 * - Resolve @throws references to actual ClassWrapper instances when possible
 *
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait CanThrow
{
    /**
     * Returns the @throws tags from the docblock.
     *
     * @return DocBlock\Tags\Throws[]|null Array of throws tags, or null if none
     */
    public function getThrows(): ?array
    {
        /** @var ?DocBlock\Tags\Throws[] */
        $throws = $this->getDocBlockTags('throws');

        if ($throws === null) {
            return null;
        }

        return $throws;
    }

    /**
     * Returns the @throws tags from the docblock, re-parsed with alternative context if available.
     *
     * For methods defined in traits, the exceptions are often imported in the trait file,
     * not in the class using the trait. This method re-parses the @throws tags using
     * the alternative context (from the trait file) when available.
     *
     * @return DocBlock\Tags\Throws[]|null Array of throws tags, or null if none
     */
    protected function getThrowsWithAlternativeContext(): ?array
    {
        // If we have an alternative context (trait/parent file), re-parse throws tags with it
        if ($this instanceof ClassElementWrapper
            && $this->alternativeDocBlockContext !== null
            && $this->docBlock !== null
        ) {
            $docComment = $this->reflection->getDocComment();
            if ($docComment !== false) {
                // Re-parse the docblock with the alternative context
                $alternativeDocBlock = Util::getDocBlocFactory()->create($docComment, $this->alternativeDocBlockContext);

                /** @var ?DocBlock\Tags\Throws[] */
                $throws = $alternativeDocBlock->getTagsByName('throws');

                return empty($throws) ? null : $throws;
            }
        }

        return $this->getThrows();
    }

    /**
     * Returns resolved @throws tags with linked exception classes.
     *
     * Each item in the returned array contains:
     * - 'destination': The ClassWrapper for the exception (if in code index) or string
     * - 'name': The exception name
     * - 'tag': The original DocBlock Throws tag
     *
     * @throws LogicException If tag resolution encounters an unexpected type
     *
     * @return array<int, array{destination: \JulienBoudry\PhpReference\Reflect\ClassElementWrapper|string, name: string, tag: DocBlock\Tags\Throws}>|null
     */
    public function getResolvedThrowsTags(): ?array
    {
        // Use alternative context for throws tags (for trait methods with locally imported exceptions)
        $throwsTags = $this->getThrowsWithAlternativeContext();

        return $this->resolveTags($throwsTags); // @phpstan-ignore return.type
    }
}

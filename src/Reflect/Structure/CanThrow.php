<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use LogicException;
use phpDocumentor\Reflection\DocBlock;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait CanThrow
{
    /**
     * @return ?DocBlock\Tags\Throws[]
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
     * @throws LogicException
     *
     * @return ?array<int, array{destination: \JulienBoudry\PhpReference\Reflect\ClassElementWrapper|string, name: string, tag: DocBlock\Tags\Throws}>
     */
    public function getResolvedThrowsTags(): ?array
    {
        $throwsTags = $this->getThrows();

        return $this->resolveTags($throwsTags); // @phpstan-ignore return.type
    }
}

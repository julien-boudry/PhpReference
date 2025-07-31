<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait CanThrow
{
    /**
     * @return ?\phpDocumentor\Reflection\DocBlock\Tags\Throws[]
     */
    public function getThrows(): ?array
    {
        /** @var ?\phpDocumentor\Reflection\DocBlock\Tags\Throws[] */
        $throws = $this->getDocBlockTags('throws');
        if ($throws === null) {
            return null;
        }

        return $throws;
    }
}

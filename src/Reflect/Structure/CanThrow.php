<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\FunctionWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\MethodWrapper
 */
trait CanThrow
{
    public function getThrows(): ?array
    {
        $throws = $this->getDocBlockTags('throws');
        if ($throws === null) {
            return null;
        }

        return $throws;
    }
}
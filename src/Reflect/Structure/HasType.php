<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

use JulienBoudry\PhpReference\Execution;
use JulienBoudry\PhpReference\UrlLinker;
use JulienBoudry\PhpReference\Util;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\PropertyWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\ParameterWrapper
 */
trait HasType
{
    /**
     * Returns the type of the element.
     */
    public function getType(): ?string
    {
        $type = $this->reflection->getType();

        return $type ? (string) $type : null;
    }

    public function getTypeMd(UrlLinker $urlLinker): ?string
    {
        return Util::getTypeMd($this->reflection->getType(), $urlLinker);
    }
}

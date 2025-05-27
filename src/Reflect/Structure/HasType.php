<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Structure;

/**
 * @mixin \JulienBoudry\PhpReference\Reflect\PropertyWrapper
 * @mixin \JulienBoudry\PhpReference\Reflect\ParameterWrapper
 */
trait HasType
{
    /**
     * Returns the type of the element.
     */
    public function getType(): string
    {
        $type = $this->reflection->getType();
        return $type ? (string) $type : '';
    }
}
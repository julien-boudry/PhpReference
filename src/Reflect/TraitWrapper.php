<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

/**
 * Wrapper for PHP trait reflection.
 *
 * Extends ClassWrapper with trait-specific type identification.
 * Traits have most functionality from ClassWrapper but with
 * different output path naming.
 *
 * @see ClassWrapper For base functionality
 */
class TraitWrapper extends ClassWrapper
{
    /**
     * The element type identifier for output paths.
     */
    public const string TYPE = 'trait';
}

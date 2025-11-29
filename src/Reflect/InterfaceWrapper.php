<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

/**
 * Wrapper for PHP interface reflection.
 *
 * Extends ClassWrapper with interface-specific type identification.
 * Interfaces have most functionality from ClassWrapper but with
 * different output path naming.
 *
 * @see ClassWrapper For base functionality
 */
class InterfaceWrapper extends ClassWrapper
{
    /**
     * The element type identifier for output paths.
     */
    public const string TYPE = 'interface';
}

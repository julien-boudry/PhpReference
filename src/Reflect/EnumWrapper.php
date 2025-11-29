<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use ReflectionEnum;
use ReflectionEnumBackedCase;

/**
 * Wrapper for PHP enum reflection with enhanced documentation capabilities.
 *
 * Extends ClassWrapper to provide enum-specific functionality including:
 * - Backing type detection (for backed enums)
 * - Case enumeration in signatures
 * - Backed value display for backed enum cases
 *
 * @see ClassWrapper For base class functionality
 */
class EnumWrapper extends ClassWrapper
{
    /**
     * The element type identifier for output paths.
     */
    public const string TYPE = 'enum';

    /**
     * The underlying ReflectionEnum.
     */
    public ReflectionEnum $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Checks if this is a backed enum (int or string backing type).
     */
    public function isBacked(): bool
    {
        return $this->reflection->isBacked();
    }

    /**
     * Returns the backing type for backed enums.
     *
     * @throws \LogicException If called on a non-backed enum
     */
    public function getBackedType(): string
    {
        if (! $this->isBacked()) {
            throw new \LogicException('This enum is not backed, so it has no backing type.');
        }

        $type = $this->reflection->getBackingType();

        return (string) $type;
    }

    /**
     * Generates the heritage portion of the enum signature.
     *
     * Includes the backing type for backed enums.
     */
    protected function getHeritageHeadSignature(): string
    {
        $backed = $this->isBacked() ? ': ' . $this->getBackedType() : '';

        return $backed . parent::getHeritageHeadSignature();
    }

    /**
     * Generates the inside-enum portion of the signature (cases and members).
     *
     * @param bool $onlyApi Whether to include only API elements
     */
    protected function getInsideClassSignature(bool $onlyApi): string
    {
        $signature = parent::getInsideClassSignature($onlyApi);

        $casesSignatures = '';

        foreach ($this->reflection->getCases() as $case) {
            $casesSignatures .= '    case ' . $case->getName();

            if ($case instanceof ReflectionEnumBackedCase) {
                $backingValue = $case->getBackingValue();
                $backingValue = \is_string($backingValue) ? '"' . $backingValue . '"' : (string) $backingValue;

                $casesSignatures .= ' = ' . $backingValue;
            }

            $casesSignatures .= ";\n";
        }

        return $casesSignatures . $signature;
    }
}

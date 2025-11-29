<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Capabilities;

/**
 * Interface for wrappers that can generate a code signature.
 *
 * This interface is implemented by wrappers representing PHP constructs
 * that have a definable signature, such as classes, methods, functions,
 * properties, and parameters.
 *
 * The signature is a string representation suitable for documentation,
 * showing visibility, type, name, and other relevant modifiers.
 *
 * @see ClassWrapper For class signature (includes members)
 * @see MethodWrapper For method signature
 * @see PropertyWrapper For property signature
 * @see ParameterWrapper For parameter signature
 */
interface SignatureInterface
{
    /**
     * Returns the code signature as a string.
     *
     * The exact format varies by element type but generally includes
     * visibility modifiers, type information, and the element name.
     */
    public function getSignature(): string;
}

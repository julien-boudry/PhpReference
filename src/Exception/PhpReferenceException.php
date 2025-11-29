<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Base exception class for all PhpReference-specific exceptions.
 *
 * All custom exceptions in the PhpReference library extend this class,
 * allowing for easy catching of any library-specific exception with
 * a single catch block.
 */
class PhpReferenceException extends \Exception {}

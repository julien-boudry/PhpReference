<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Exception;

/**
 * Exception thrown when the configuration is invalid or missing required values.
 *
 * This exception is thrown when:
 * - A required configuration key is missing
 * - A configuration value has an invalid format or type
 * - An unknown API definition name is specified
 *
 * @see Config For configuration handling
 */
class InvalidConfigurationException extends PhpReferenceException {}

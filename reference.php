<?php declare(strict_types=1);

/**
 * Configuration file for PhpReference
 *
 * This file contains the default configuration for generating documentation.
 * Command line arguments will override these values if provided.
 *
 * Example usage:
 * - php bin/php-reference generate:documentation (uses all config values)
 * - php bin/php-reference generate:documentation MyNamespace (overrides namespace)
 * - php bin/php-reference generate:documentation --output=/custom/path (overrides output)
 */

return [
    // The namespace to generate documentation for
    // Can be overridden with: php bin/php-reference generate:documentation MyNamespace
    'namespace' => 'CondorcetPHP\\Condorcet',

    // Output directory for generated documentation
    // Can be overridden with: --output=/path/to/output or -o /path/to/output
    'output' => getcwd() . DIRECTORY_SEPARATOR . 'output',

    // Don't clean the output directory before generating documentation
    // Can be overridden with: --append or -a
    'append' => false,

    // Include all public classes, methods, and properties in the documentation,
    // even those not marked with @api tags
    // Can be overridden with: --all-public or -p
    'all-public' => false,
];

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Definition\HasTagApi;

/**
 * Configuration file for PhpReference.
 *
 * This file contains the default configuration for generating documentation.
 * Command line arguments will override these values if provided.
 *
 * Example usage:
 * - php bin/php-reference generate:documentation (uses all config values)
 * - php bin/php-reference generate:documentation MyNamespace (overrides namespace)
 * - php bin/php-reference generate:documentation --output=/custom/path (overrides output)
 * - php bin/php-reference generate:documentation --api=public (overrides API definition)
 */

return [
    // The namespace to generate documentation for
    // Can be overridden with: php bin/php-reference generate:documentation MyNamespace
    'namespace' => 'CondorcetPHP\\Condorcet',

    // Output directory for generated documentation
    // Can be overridden with: --output=/path/to/output or -o /path/to/output
    'output' => getcwd() . \DIRECTORY_SEPARATOR . 'output',

    // Don't clean the output directory before generating documentation
    // Can be overridden with: --append or -a
    'append' => false,

    // Never prompt for user interaction (e.g., yes/no prompts), especially if the output directory is not empty
    'no-interaction' => false,

    // API definition to determine which elements should be included in documentation
    // Can be an instance of PublicApiDefinitionInterface or will be resolved from string via CLI
    // Available CLI values: 'api' (default), 'public'
    // Can be overridden with: --api=public or --api=api
    'api' => new HasTagApi,

    // Base URL for source code links (e.g., https://github.com/user/repo/blob/main)
    // If not set, no source links will be generated
    // Can be overridden with: --source-url-base=https://github.com/user/repo/blob/main
    'source-url-base' => 'https://github.com/julien-boudry/Condorcet/blob/master',
];

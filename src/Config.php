<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Definition\{HasTagApi, IsPubliclyAccessible, PublicApiDefinitionInterface};
use JulienBoudry\PhpReference\Exception\InvalidConfigurationException;

/**
 * Configuration manager for the documentation generator.
 *
 * This class handles loading and managing configuration from a PHP configuration
 * file (typically reference.php). It supports merging CLI arguments with file-based
 * configuration, with CLI arguments taking priority.
 *
 * Configuration keys include:
 * - namespace: The PHP namespace to document
 * - output: Output directory for generated documentation
 * - api: The API definition strategy to use
 * - index-file-name: Name of the index file (default: 'readme')
 * - source-url-base: Base URL for source code links
 * - no-interaction: Whether to disable interactive prompts
 * - append: Whether to append to existing documentation
 *
 * @see GenerateDocumentationCommand For how configuration is used
 */
class Config
{
    /**
     * Internal configuration storage.
     *
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * Creates a new Config instance, optionally loading from a configuration file.
     *
     * If no path is provided, looks for 'reference.php' in the current working directory.
     *
     * @param string|null $configPath Path to the configuration file, or null to use default
     */
    public function __construct(?string $configPath = null)
    {
        $configPath = $configPath ?? getcwd() . \DIRECTORY_SEPARATOR . 'reference.php';

        if (file_exists($configPath)) {
            $this->config = require $configPath;
        }
    }

    /**
     * Retrieves a configuration value.
     *
     * @param string $key     The configuration key to retrieve
     * @param mixed  $default Value to return if the key doesn't exist
     *
     * @return mixed The configuration value or the default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Sets a configuration value.
     *
     * Typically used for CLI argument overrides.
     *
     * @param string $key   The configuration key to set
     * @param mixed  $value The value to set
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Checks if a configuration key exists.
     *
     * @param string $key The configuration key to check
     *
     * @return bool True if the key exists (even if null), false otherwise
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->config);
    }

    /**
     * Returns all configuration values as an array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Merges CLI arguments into the configuration.
     *
     * CLI arguments take priority over file-based configuration.
     * Only non-null values are merged.
     *
     * @param array<string, string|bool|null> $cliArgs Associative array of CLI arguments
     */
    public function mergeWithCliArgs(array $cliArgs): void
    {
        foreach ($cliArgs as $key => $value) {
            if ($value !== null) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Resolves and returns the API definition instance.
     *
     * The API definition can be configured as either a string (class name)
     * or an instance of PublicApiDefinitionInterface. String values are
     * resolved to their corresponding class instances (case-insensitive).
     *
     * Available string values:
     * - 'IsPubliclyAccessible': Includes all public elements
     * - 'HasTagApi': Requires explicit @api tags (strictest)
     *
     * @param PublicApiDefinitionInterface|null $default Default definition if none configured
     *
     * @throws InvalidConfigurationException If the string value doesn't match a known definition
     *
     * @return PublicApiDefinitionInterface|null The resolved API definition
     */
    public function getApiDefinition(?PublicApiDefinitionInterface $default = null): ?PublicApiDefinitionInterface
    {
        $apiConfig = $this->get('api', $default);

        if ($apiConfig instanceof PublicApiDefinitionInterface) {
            return $apiConfig;
        }

        if (\is_string($apiConfig) && ! empty($apiConfig)) {
            return match (mb_strtolower($apiConfig)) {
                mb_strtolower('IsPubliclyAccessible') => new IsPubliclyAccessible,
                mb_strtolower('HasTagApi') => new HasTagApi,
                default => throw new InvalidConfigurationException("Unknown API definition '{$apiConfig}'. Valid options: IsPubliclyAccessible, HasTagApi"),
            };
        }

        return null;
    }

    /**
     * Returns the base URL for generating source code links.
     *
     * The URL is normalized to remove trailing slashes. This base URL
     * is used to generate links like "View source on GitHub" in the
     * documentation.
     *
     * @return string|null The source URL base, or null if not configured
     */
    public function getSourceUrlBase(): ?string
    {
        $sourceUrlBase = $this->get('source-url-base');

        if (\is_string($sourceUrlBase) && ! empty($sourceUrlBase)) {
            return rtrim($sourceUrlBase, '/');
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Definition\{HasTagApi, IsPubliclyAccessible, PublicApiDefinitionInterface};

class Config
{
    /** @var array<string, mixed> */
    private array $config = [];

    public function __construct(?string $configPath = null)
    {
        $configPath = $configPath ?? getcwd() . \DIRECTORY_SEPARATOR . 'reference.php';

        if (file_exists($configPath)) {
            $this->config = require $configPath;
        }
    }

    /**
     * Get a configuration value with optional default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set a configuration value (for CLI overrides).
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Check if a configuration key exists.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->config);
    }

    /**
     * Get all configuration as array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Merge configuration with CLI arguments, giving priority to CLI.
     *
     * @param  array<string, string|bool|null>  $cliArgs  Associative array of CLI arguments
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
     * Get the API definition, resolving from string if necessary.
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
                default => throw new \InvalidArgumentException("Unknown API definition '{$apiConfig}'"),
            };
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toContainAll', function (array $needles) {
    foreach ($needles as $needle) {
        test()->assertTrue(
            str_contains($this->value, $needle),
            "Failed asserting that '{$this->value}' contains '{$needle}'"
        );
    }

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Create a temporary config file for testing.
 */
function createTempConfig(array $config): string
{
    $path = sys_get_temp_dir() . '/php-reference-test-' . uniqid() . '.php';
    file_put_contents($path, '<?php return ' . var_export($config, true) . ';');

    return $path;
}

/**
 * Clean up temporary config file.
 */
function removeTempConfig(string $path): void
{
    if (file_exists($path)) {
        unlink($path);
    }
}

/**
 * Helper function to create an Execution instance for testing.
 * This initializes Execution::$instance which is required by Reflection wrappers.
 */
function createExecutionFixture(
    ?string $namespace = null,
    ?string $outputDir = null,
    ?\JulienBoudry\PhpReference\Definition\PublicApiDefinitionInterface $apiDefinition = null
): \JulienBoudry\PhpReference\Execution {
    // Default to a smaller, specific namespace for testing
    // Log namespace is smaller and faster to index
    $namespace ??= 'JulienBoudry\\PhpReference\\Log';
    $outputDir ??= sys_get_temp_dir() . '/php-reference-test';
    $apiDefinition ??= new \JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

    // Create config with the API definition
    $config = new \JulienBoudry\PhpReference\Config;
    $config->set('api', $apiDefinition);

    // Create and return Execution instance (this sets Execution::$instance)
    return new \JulienBoudry\PhpReference\Execution(
        codeIndex: new \JulienBoudry\PhpReference\CodeIndex($namespace),
        outputDir: $outputDir,
        config: $config,
    );
}

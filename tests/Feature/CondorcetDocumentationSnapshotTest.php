<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Command\GenerateDocumentationCommand;
use Laravel\Prompts\Prompt;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Static cache for generated documentation files.
 * This allows generating documentation once and reusing across all dataset tests.
 */
final class CondorcetDocumentationCache
{
    private static ?array $files = null;
    private static ?string $outputDir = null;
    private static ?string $configPath = null;

    public static function getFiles(): array
    {
        if (self::$files === null) {
            self::generate();
        }

        return self::$files;
    }

    private static function generate(): void
    {
        self::$outputDir = sys_get_temp_dir() . '/php-reference-condorcet-snapshot-test-' . uniqid();
        mkdir(self::$outputDir, 0755, true);

        // Create config file
        $configContent = sprintf(
            '<?php return [
                "namespace" => "CondorcetPHP\\\\Condorcet",
                "output" => %s,
                "append" => false,
                "no-interaction" => true,
                "api" => new \\JulienBoudry\\PhpReference\\Definition\\HasTagApi,
                "source-url-base" => "https://github.com/julien-boudry/Condorcet/blob/master",
            ];',
            var_export(self::$outputDir, true)
        );

        self::$configPath = sys_get_temp_dir() . '/php-reference-condorcet-config-' . uniqid() . '.php';
        file_put_contents(self::$configPath, $configContent);

        // Redirect Laravel Prompts output to a buffer
        Prompt::setOutput(new BufferedOutput);

        // Generate documentation
        $application = new Application;
        $command = new GenerateDocumentationCommand;
        $application->addCommand($command);

        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(
            ['--config' => self::$configPath],
            ['interactive' => false, 'capture_stderr_separately' => true]
        );

        if ($exitCode !== 0) {
            throw new RuntimeException('Documentation generation failed with exit code: ' . $exitCode);
        }

        // Collect all generated files
        self::$files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$outputDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $relativePath = str_replace(self::$outputDir . '/', '', $file->getPathname());
                self::$files[$relativePath] = file_get_contents($file->getPathname());
            }
        }

        ksort(self::$files);

        // Register cleanup
        register_shutdown_function([self::class, 'cleanup']);
    }

    public static function cleanup(): void
    {
        if (self::$outputDir && is_dir(self::$outputDir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(self::$outputDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }

            rmdir(self::$outputDir);
        }

        if (self::$configPath && file_exists(self::$configPath)) {
            unlink(self::$configPath);
        }

        self::$files = null;
        self::$outputDir = null;
        self::$configPath = null;
    }
}

// Build dataset from generated files
dataset('condorcet_documentation_files', function () {
    $files = CondorcetDocumentationCache::getFiles();

    foreach ($files as $relativePath => $content) {
        yield $relativePath => [$relativePath, $content];
    }
});

describe('Condorcet Documentation Snapshots', function (): void {
    it('generates {file}', function (string $relativePath, string $content): void {
        expect($content)->toMatchSnapshot();
    })->with('condorcet_documentation_files');
});


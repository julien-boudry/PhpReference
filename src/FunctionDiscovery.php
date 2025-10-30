<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory};

/**
 * Discovers standalone functions in a namespace using PHP Parser.
 */
class FunctionDiscovery
{
    /**
     * Find all standalone functions declared in the given namespace.
     *
     * @return array<string> Array of fully qualified function names
     */
    public static function getFunctionsInNamespace(string $namespace): array
    {
        // Get all PHP files in the namespace
        $files = self::getPhpFilesInNamespace($namespace);

        $functions = [];
        $parser = (new ParserFactory)->createForNewestSupportedVersion();

        foreach ($files as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            try {
                $ast = $parser->parse($code);
                if ($ast === null) {
                    continue;
                }

                // Extract functions from this file
                $visitor = new FunctionVisitor;
                $traverser = new NodeTraverser;
                $traverser->addVisitor($visitor);
                $traverser->traverse($ast);

                // Get the namespace from the file
                $fileNamespace = $visitor->getNamespace();

                // Add functions with their fully qualified names
                foreach ($visitor->getFunctions() as $functionName) {
                    $fqn = $fileNamespace ? $fileNamespace . '\\' . $functionName : $functionName;

                    // Only include functions in our target namespace
                    if (str_starts_with($fqn, $namespace)) {
                        $functions[] = $fqn;
                    }
                }
            } catch (\Exception $e) {
                // Skip files that can't be parsed
                continue;
            }
        }

        return array_unique($functions);
    }

    /**
     * Get all PHP files in the given namespace.
     *
     * @return array<string> Array of file paths
     */
    protected static function getPhpFilesInNamespace(string $namespace): array
    {
        // Convert namespace to directory path
        // We'll use Composer's autoloader to find the files
        $autoloadFiles = get_included_files();
        $composerAutoloadPath = null;

        foreach ($autoloadFiles as $file) {
            if (str_ends_with($file, 'vendor/autoload.php')) {
                $composerAutoloadPath = \dirname($file);

                break;
            }
        }

        if ($composerAutoloadPath === null) {
            return [];
        }

        // Read composer.json to get PSR-4 mappings
        $composerJsonPath = \dirname($composerAutoloadPath) . '/composer.json';
        if (!file_exists($composerJsonPath)) {
            return [];
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        if (!isset($composerJson['autoload']['psr-4']) && !isset($composerJson['autoload-dev']['psr-4'])) {
            return [];
        }

        $files = [];
        $projectRoot = \dirname($composerAutoloadPath);

        // Merge autoload and autoload-dev PSR-4 mappings
        $psr4Mappings = array_merge(
            $composerJson['autoload']['psr-4'] ?? [],
            $composerJson['autoload-dev']['psr-4'] ?? []
        );

        foreach ($psr4Mappings as $prefix => $dirs) {
            // Check if this PSR-4 prefix matches our namespace
            $prefix = rtrim($prefix, '\\');
            if (str_starts_with($namespace, $prefix)) {
                foreach ((array) $dirs as $dir) {
                    $fullPath = $projectRoot . '/' . rtrim($dir, '/');

                    // Convert namespace to relative path
                    $namespaceSuffix = substr($namespace, \strlen($prefix));
                    $namespacePath = str_replace('\\', '/', trim($namespaceSuffix, '\\'));
                    $searchPath = $namespacePath ? $fullPath . '/' . $namespacePath : $fullPath;

                    if (is_dir($searchPath)) {
                        $files = array_merge($files, self::scanDirectory($searchPath));
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Recursively scan a directory for PHP files.
     *
     * @return array<string> Array of file paths
     */
    protected static function scanDirectory(string $directory): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

/**
 * AST visitor to extract function declarations from PHP code.
 */
class FunctionVisitor extends NodeVisitorAbstract
{
    /** @var array<string> */
    private array $functions = [];

    private ?string $namespace = null;

    public function enterNode(Node $node): int|Node|array|null
    {
        // Track the current namespace
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name?->toString();
        }

        // Find function declarations (not class methods)
        if ($node instanceof Node\Stmt\Function_) {
            $this->functions[] = $node->name->toString();
        }

        return null;
    }

    /**
     * @return array<string>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}

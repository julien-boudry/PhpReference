<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory};

/**
 * Discovers standalone PHP functions within a namespace.
 *
 * While ClassFinder handles class discovery, PHP doesn't have a built-in
 * way to discover functions in a namespace. This class uses PHP Parser
 * to analyze source files and find function declarations.
 *
 * The discovery process:
 * 1. Find PHP files in the namespace using Composer's PSR-4 autoload mappings
 * 2. Parse each file to extract function declarations
 * 3. Filter functions to those within the target namespace
 *
 * @see FunctionVisitor The AST visitor used to extract function declarations
 */
class FunctionDiscovery
{
    /**
     * Finds all standalone functions declared in a namespace.
     *
     * This method discovers functions by:
     * 1. Locating PHP files using PSR-4 mappings from composer.json
     * 2. Parsing each file with PHP Parser
     * 3. Extracting function declarations using FunctionVisitor
     * 4. Filtering to only include functions in the target namespace
     *
     * @param $namespace The namespace to search for functions
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
     * Locates all PHP files within a namespace using Composer PSR-4 mappings.
     *
     * This method reads composer.json to find the directory mapping for the
     * given namespace, then recursively scans that directory for PHP files.
     *
     * @param $namespace The namespace to find files for
     *
     * @return array<string> Array of absolute file paths
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
     * Recursively scans a directory for PHP files.
     *
     * @param $directory The directory to scan
     *
     * @return array<string> Array of absolute file paths
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
 * PHP Parser AST visitor that extracts function declarations from source code.
 *
 * This visitor traverses the AST produced by PHP Parser and collects:
 * - The namespace declaration (if any)
 * - All standalone function declarations (not methods)
 *
 * @see FunctionDiscovery For how this visitor is used
 */
class FunctionVisitor extends NodeVisitorAbstract
{
    /**
     * Names of all discovered functions.
     *
     * @var array<string>
     */
    private array $functions = [];

    /**
     * The namespace declared in the file, or null if global.
     */
    private ?string $namespace = null;

    /**
     * Processes each node in the AST to find namespace and function declarations.
     *
     * @param Node $node The current AST node being visited
     *
     * @return int|Node|array<Node>|null Visitor return value
     */
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
     * Returns the list of discovered function names.
     *
     * @return array<string> Function names (without namespace prefix)
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * Returns the namespace declared in the file.
     *
     * @return string|null The namespace, or null if the file is in global scope
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}

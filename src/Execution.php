<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Definition\{IsPubliclyAccessible, PublicApiDefinitionInterface};
use JulienBoudry\PhpReference\Reflect\{ClassWrapper, FunctionWrapper};
use JulienBoudry\PhpReference\Writer\{AbstractWriter, ClassPageWriter, FunctionPageWriter, MethodPageWriter, NamespacePageWriter, PropertyPageWriter, PublicApiSummaryWriter};
use JulienBoudry\PhpReference\Log\ErrorCollector;

/**
 * Singleton orchestrator for the documentation generation process.
 *
 * The Execution class coordinates the entire documentation generation workflow.
 * It maintains the global state needed throughout the generation process and
 * provides methods to build different parts of the documentation.
 *
 * The class is a singleton, accessible via Execution::$instance. This pattern
 * is used because many wrapper classes need access to the execution context
 * (code index, error collector, API definition) without passing them through
 * every method call.
 *
 * Generation workflow:
 * 1. Create Execution instance with CodeIndex and configuration
 * 2. Call buildIndex() to generate the main API summary page
 * 3. Call buildNamespacePages() to generate namespace-level documentation
 * 4. Call buildPages() to generate individual class, method, and property pages
 *
 * @see CodeIndex For the source of elements to document
 * @see PublicApiDefinitionInterface For API inclusion rules
 */
final class Execution
{
    /**
     * Global singleton instance accessible throughout the application.
     */
    public static self $instance;

    /**
     * Collects non-fatal errors and warnings during generation.
     */
    public readonly ErrorCollector $errorCollector;

    /**
     * Elements that will be documented (classes and functions in the public API).
     *
     * @var array<ClassWrapper|FunctionWrapper>
     */
    public readonly array $mainPhpNodes;

    /**
     * Tracks all paths that have been written to avoid duplicates.
     *
     * @var array<int, string>
     */
    public private(set) array $writedPages = [];

    /**
     * The API definition used to determine what elements are part of the public API.
     */
    public readonly PublicApiDefinitionInterface $publicApiDefinition;

    /**
     * Creates a new Execution instance and sets it as the global singleton.
     *
     * @param CodeIndex $codeIndex The indexed namespace containing elements to document
     * @param string    $outputDir The directory where documentation will be written
     * @param Config    $config    The configuration for this execution
     */
    public function __construct(
        public readonly CodeIndex $codeIndex,
        public readonly string $outputDir,
        public readonly Config $config,
    ) {
        $this->errorCollector = new ErrorCollector;

        self::$instance = $this;

        $this->publicApiDefinition = $this->config->getApiDefinition(default: new IsPubliclyAccessible);
        $this->mainPhpNodes = $codeIndex->apiElementsList;
    }

    /**
     * Builds the main API summary index page.
     *
     * This generates the root documentation file (typically readme.md) that
     * provides an overview of all documented elements.
     *
     * @param string $fileName The name for the index file (without extension)
     *
     * @return static Returns self for method chaining
     */
    public function buildIndex(string $fileName): static
    {
        // Generate index page
        $this->writePage(new PublicApiSummaryWriter(codeIndex: $this->codeIndex, filePath: '/' . $fileName));

        return $this;
    }

    /**
     * Builds documentation pages for each namespace.
     *
     * Each namespace gets its own directory with an index file listing
     * all classes and functions within that namespace.
     *
     * @param string        $indexFileName         The name for namespace index files
     * @param callable|null $afterElementCallback  Optional callback invoked after each namespace is processed
     *
     * @return static Returns self for method chaining
     */
    public function buildNamespacePages(string $indexFileName, ?callable $afterElementCallback = null): static
    {
        // Generate a page for each namespace
        foreach ($this->codeIndex->namespaces as $namespace) {
            $this->writePage(new NamespacePageWriter($namespace, $indexFileName));

            if ($afterElementCallback) {
                $afterElementCallback();
            }
        }

        return $this;
    }

    /**
     * Builds individual documentation pages for all API elements.
     *
     * For each class in the public API, this generates:
     * - A main class page with overview and signatures
     * - Individual pages for each public method
     * - Individual pages for each public property
     *
     * For each function in the public API, this generates a function page.
     *
     * @param callable|null $afterElementCallback Optional callback invoked after each element is processed
     *
     * @throws \LogicException If an unsupported element type is encountered
     *
     * @return static Returns self for method chaining
     */
    public function buildPages(?callable $afterElementCallback = null): static
    {
        foreach ($this->mainPhpNodes as $element) {
            if ($element instanceof ClassWrapper) {
                // Generate class page
                $this->writePage(new ClassPageWriter($element));

                // Generate method pages
                foreach ($element->getAllApiMethods() as $method) {
                    $this->writePage(new MethodPageWriter($method));
                }

                // Generate property pages
                foreach ($element->getAllApiProperties() as $property) {
                    $this->writePage(new PropertyPageWriter($property));
                }
            } elseif ($element instanceof FunctionWrapper) {
                // Generate function page
                $this->writePage(new FunctionPageWriter($element));
            } else {
                throw new \LogicException('Unsupported element type: ' . $element::class);
            }

            if ($afterElementCallback) {
                $afterElementCallback();
            }
        }

        return $this;
    }

    /**
     * Writes a documentation page to the output directory.
     *
     * Tracks written paths to prevent duplicate writes. A page is only
     * written if its path hasn't been written before in this execution.
     *
     * @param AbstractWriter $writer The writer responsible for generating and writing the page
     */
    protected function writePage(AbstractWriter $writer): void
    {
        $writePath = $writer->writePath;

        if (! \in_array($writePath, $this->writedPages, true)) {
            $this->writedPages[] = $writer->write();
        }
    }
}

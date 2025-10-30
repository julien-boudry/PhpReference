<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Definition\{IsPubliclyAccessible, PublicApiDefinitionInterface};
use JulienBoudry\PhpReference\Reflect\{ClassWrapper, FunctionWrapper};
use JulienBoudry\PhpReference\Writer\{AbstractWriter, ClassPageWriter, FunctionPageWriter, MethodPageWriter, NamespacePageWriter, PropertyPageWriter, PublicApiSummaryWriter};
use JulienBoudry\PhpReference\Log\ErrorCollector;

final class Execution
{
    public static self $instance;

    public readonly ErrorCollector $errorCollector;

    /** @var array<ClassWrapper|FunctionWrapper> */
    public readonly array $mainPhpNodes;

    /** @var array<int, string> */
    public private(set) array $writedPages = [];

    public readonly PublicApiDefinitionInterface $publicApiDefinition;

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

    public function buildIndex(string $fileName): static
    {
        // Generate index page
        $this->writePage(new PublicApiSummaryWriter(codeIndex: $this->codeIndex, filePath: '/' . $fileName));

        return $this;
    }

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

    protected function writePage(AbstractWriter $writer): void
    {
        $writePath = $writer->writePath;

        if (! \in_array($writePath, $this->writedPages, true)) {
            $this->writedPages[] = $writer->write();
        }
    }
}

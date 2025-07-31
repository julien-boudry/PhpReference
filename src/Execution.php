<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Definition\PublicApiDefinitionInterface;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Writer\{AbstractWriter, ClassPageWriter, MethodPageWriter, PropertyPageWriter, PublicApiSummaryWriter};

final class Execution
{
    public static self $instance;

    /** @var ClassWrapper[] */
    public readonly array $mainPhpNodes;

    /* @var array<string> */
    public private(set) array $writedPages = [];

    public function __construct(
        public readonly CodeIndex $codeIndex,
        public readonly string $outputDir,
        public readonly PublicApiDefinitionInterface $publicApiDefinition,
    ) {
        self::$instance = $this;
        $this->mainPhpNodes = $codeIndex->getApiClasses();
    }

    public function buildIndex(): static
    {
        // Generate index page
        new PublicApiSummaryWriter($this->codeIndex);

        return $this;
    }

    public function buildPages(?callable $afterElementCallback = null): static
    {
        foreach ($this->mainPhpNodes as $class) {
            // Generate class page
            $this->writePage(new ClassPageWriter($class));

            // Generate method pages

            foreach ($class->getAllApiMethods() as $method) {
                $this->writePage(new MethodPageWriter($method));
            }

            // Generate property pages
            foreach ($class->getAllApiProperties() as $property) {
                $this->writePage(new PropertyPageWriter($property));
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

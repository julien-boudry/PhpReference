<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Definition\PublicApiDefinitionInterface;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Writer\PropertyPageWriter;
use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;

final class Execution
{
    public static self $instance;

    /** @var ClassWrapper[] */
    public readonly array $elements;

    public function __construct (
        public readonly CodeIndex $codeIndex,
        public readonly string $outputDir,
        public readonly PublicApiDefinitionInterface $publicApiDefinition,
    ) {
        self::$instance = $this;
        $this->elements = $codeIndex->getApiClasses();
    }

    public function buildIndex(): static
    {
        // Generate index page
        new PublicApiSummaryWriter($this->codeIndex);

        return $this;
    }

    public function buildPages(?callable $afterElementCallback = null): static
    {
        foreach ($this->elements as $class) {
            // Generate class page
            new ClassPageWriter($class);

            // Generate method pages

            foreach ($class->getAllApiMethods() as $method) {
                new MethodPageWriter($method);
            }

            // Generate property pages
            foreach ($class->getAllApiProperties() as $property) {
                new PropertyPageWriter($property);
            }

            if ($afterElementCallback) {
                $afterElementCallback();
            }
        }

        return $this;
    }
}
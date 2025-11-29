<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use Latte\{ContentType, Engine};
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Abstract base class for documentation page writers.
 *
 * This class provides the foundation for all documentation page generators.
 * It manages:
 * - The Latte template engine for rendering Markdown content
 * - The Flysystem filesystem abstraction for writing output files
 * - Common write path management
 *
 * Concrete writers extend this class and implement makeContent() to generate
 * their specific documentation content using Latte templates.
 *
 * @see ClassPageWriter For class documentation pages
 * @see MethodPageWriter For method documentation pages
 * @see PropertyPageWriter For property documentation pages
 * @see FunctionPageWriter For function documentation pages
 * @see NamespacePageWriter For namespace index pages
 * @see PublicApiSummaryWriter For the main API summary
 */
abstract class AbstractWriter
{
    /**
     * Path to the template directory containing Latte templates.
     */
    public const TEMPLATE_DIR = __DIR__ . '/../Template';

    /**
     * Output directory for generated documentation.
     *
     * This is set globally by the command before generation begins.
     */
    public static string $outputDir = __DIR__ . '/../../output';

    /**
     * Cached Flysystem filesystem instance.
     */
    protected static Filesystem $filesystem;

    /**
     * The path where this page will be written, relative to output directory.
     */
    public protected(set) string $writePath = '/';

    /**
     * Cached Latte template engine instance.
     */
    protected static Engine $latte;

    /**
     * The generated content to be written.
     */
    public readonly string $content;

    /**
     * Returns the Flysystem filesystem instance for writing files.
     *
     * The filesystem is lazily instantiated and configured to write to
     * the output directory specified in $outputDir.
     */
    public static function getFlySystem(): Filesystem
    {
        return self::$filesystem ??= new Filesystem(new LocalFilesystemAdapter(self::$outputDir));
    }

    /**
     * Creates a new writer instance.
     *
     * Initializes the Latte template engine (if not already initialized) and
     * generates the content by calling makeContent().
     */
    public function __construct()
    {
        // Initialiser Flysystem
        self::getFlySystem();

        // Initialiser Latte
        self::$latte ??= new Engine;
        self::$latte->setStrictParsing()->setStrictTypes()->setTempDirectory(sys_get_temp_dir());
        self::$latte->setContentType(ContentType::Text); // Désactiver l'échappement pour Markdown

        // Make Content
        $this->content = $this->makeContent();
    }

    /**
     * Generates the content for this documentation page.
     *
     * Concrete writers implement this method to render their specific
     * Latte template with the appropriate input data.
     *
     * @return The generated Markdown content
     */
    abstract public function makeContent(): string;

    /**
     * Writes the generated content to the output file.
     *
     * @return The path that was written to
     */
    public function write(): string
    {
        self::getFlySystem()->write($this->writePath, $this->content);

        return $this->writePath;
    }
}

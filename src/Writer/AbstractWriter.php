<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use Latte\ContentType;
use Latte\Engine;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

abstract class AbstractWriter
{
    public const TEMPLATE_DIR = __DIR__ . '/../Template';

    public static string $outputDir = __DIR__ . '/../../output';
    protected static Filesystem $filesystem;

    public string $writePath = '/';

    protected static Engine $latte;

    public readonly string $content;

    public static function getFlySystem(): Filesystem
    {
        return self::$filesystem ??= new Filesystem(new LocalFilesystemAdapter(self::$outputDir));
    }

    public function __construct() {
        // Initialiser Flysystem
        self::getFlySystem();

        // Initialiser Latte
        self::$latte ??= new Engine;
        self::$latte->setStrictParsing()->setStrictTypes()->setTempDirectory(sys_get_temp_dir());
        self::$latte->setContentType(ContentType::Text); // Désactiver l'échappement pour Markdown

        // Make Content
        $this->content = $this->makeContent();

        // Write Content
        $this->write();
    }

    abstract function makeContent(): string;

    protected function write(): void
    {
        self::getFlySystem()->write($this->writePath, $this->content);
    }
}
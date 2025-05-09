<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\CodeIndex;
use Latte\ContentType;
use Latte\Engine;
use SplFileObject;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;

abstract class AbstractWriter
{
    public const TEMPLATE_DIR = __DIR__ . '/../Template';
    public const OUTPUT_DIR = __DIR__ . '/../../output';

    protected static Filesystem $filesystem;

    public string $writePath = '/';

    protected static Engine $latte;

    public readonly string $content;

    public function __construct(public readonly CodeIndex $codeIndex) {
        // Initialiser Flysystem
        self::$filesystem ??= new Filesystem(new LocalFilesystemAdapter(self::OUTPUT_DIR));

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
        self::$filesystem->write($this->writePath, $this->content);
    }
}
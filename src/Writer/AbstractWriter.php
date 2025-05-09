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

    protected Engine $latte;

    public readonly string $content;

    public function __construct(public readonly CodeIndex $codeIndex) {
        // Initialiser Flysystem
        self::$filesystem ??= new Filesystem(new LocalFilesystemAdapter(self::OUTPUT_DIR));

        // Initialiser Latte
        $this->latte = new Engine;
        $this->latte->setStrictParsing()->setStrictTypes()->setTempDirectory(sys_get_temp_dir());
        $this->latte->setContentType(ContentType::Text); // Désactiver l'échappement pour Markdown


        $this->content = $this->makeContent();

        $this->write();
    }

    abstract function makeContent(): string;

    protected function write(): void
    {
        try {
            // Écrire le contenu dans le fichier
            self::$filesystem->write($this->writePath, $this->content);
        } catch (UnableToWriteFile $e) {
            throw new \RuntimeException("Impossible d'écrire dans le fichier '{$this->writePath}': " . $e->getMessage());
        }
    }
}
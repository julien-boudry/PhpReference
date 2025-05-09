<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\CodeIndex;
use Latte\ContentType;
use Latte\Engine;
use SplFileObject;

abstract class AbstractWriter
{
    public const TEMPLATE_DIR = __DIR__ . '/../Template';

    protected Engine $latte;

    public function __construct(public readonly CodeIndex $codeIndex) {
        // Initialiser Latte
        $this->latte = new Engine;
                // Initialiser Latte
        $this->latte = new Engine;
        $this->latte->setStrictParsing()->setStrictTypes()->setTempDirectory(sys_get_temp_dir());
        $this->latte->setContentType(ContentType::Text); // Désactiver l'échappement pour Markdown

        $this->write();
    }

    abstract function write(): void;

    public function getBuildIndex() : string
    {
        // TODO

        return '';
    }

    public function writeTo(SplFileObject $file): void
    {
        if (!$file->isWritable()) {
            throw new \RuntimeException("The file '{$file->getPathname()}' is not writable");
        }

        $file->fwrite($this->getBuildIndex());
    }
}
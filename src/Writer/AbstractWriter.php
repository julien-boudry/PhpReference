<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\CodeIndex;
use Latte\ContentType;
use Latte\Engine;
use SplFileObject;

abstract class AbstractWriter
{
    public const TEMPLATE_DIR = __DIR__ . '/../Template';
    public const OUTPUT_DIR = __DIR__ . '/../../output';

    public const string WRITE_PATH = self::OUTPUT_DIR . '/';

    protected Engine $latte;

    public readonly string $content;

    public function __construct(public readonly CodeIndex $codeIndex) {
        // Initialiser Latte
        $this->latte = new Engine;
        $this->latte->setStrictParsing()->setStrictTypes()->setTempDirectory(sys_get_temp_dir());
        $this->latte->setContentType(ContentType::Text); // Désactiver l'échappement pour Markdown


        $this->content = $this->makeContent();

        $this->write();
    }

    abstract function makeContent(): string;

    public function getBuildIndex() : string
    {
        // TODO

        return '';
    }

    protected function write(): void
    {
        // Vérifier si le répertoire de sortie existe, sinon le créer
        if (!is_dir(self::OUTPUT_DIR)) {
            mkdir(self::OUTPUT_DIR, 0755, true);
        }

        $file = new SplFileObject(static::WRITE_PATH, 'w+');

        if (!$file->isWritable()) {
            throw new \RuntimeException("The file '{$file->getPathname()}' is not writable");
        }

        $file->fwrite($this->content);
    }
}
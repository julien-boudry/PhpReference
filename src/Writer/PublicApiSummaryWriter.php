<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Formater\ClassFormater;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Template\Input\ApiSummaryInput;
use Latte\ContentType;
use Latte\Engine;
use SplFileObject;

class PublicApiSummaryWriter extends AbstractWriter
{
    public string $writePath = '/readme.md';

    public function makeContent(): string
    {
        return $this->getBuildIndex();
    }

    /** @var array<string, ClassFormater> */
    public array $classformaters {
        get {
            $r = [];

            foreach ($this->codeIndex->getPublicClasses() as $className => $class) {
                $r[$className] = new ClassFormater($class);
            }

            return $r;
        }
    }

    public function getBuildIndex() : string
    {
        // Utiliser Latte pour générer du Markdown
        return $this->latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/api_summary.latte',
            params : new ApiSummaryInput(classes: $this->codeIndex->getPublicClasses()),
        );
    }
}
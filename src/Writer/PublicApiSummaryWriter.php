<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Formater\ClassFormater;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Template\Input\ApiSummaryInput;
use JulienBoudry\PhpReference\Template\Input\ClassPageInput;

class PublicApiSummaryWriter extends AbstractWriter
{
    public string $writePath = '/readme.md';

    public function __construct (public readonly CodeIndex $codeIndex) {
        parent::__construct();
    }

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
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/api_summary.latte',
            params : new ApiSummaryInput(
                classes: $this->codeIndex->getPublicClasses()
            ),
        );
    }
}
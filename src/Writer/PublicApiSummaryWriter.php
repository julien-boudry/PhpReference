<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Template\Input\ApiSummaryInput;

class PublicApiSummaryWriter extends AbstractWriter
{
    public function __construct(public readonly CodeIndex $codeIndex, string $filePath)
    {
        $filePath = str_replace('.md', '', $filePath);
        $filePath .= '.md';

        $this->writePath = $filePath;
        parent::__construct();
    }

    public function makeContent(): string
    {
        return $this->getBuildIndex();
    }

    public function getBuildIndex(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/api_summary.latte',
            params : new ApiSummaryInput(
                classes: $this->codeIndex->getApiClasses()
            ),
        );
    }
}

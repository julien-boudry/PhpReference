<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Template\Input\ApiSummaryInput;

/**
 * Writer for generating the main API summary page.
 *
 * This writer creates the root documentation file (typically readme.md) that
 * serves as the entry point to the generated documentation. The page includes:
 * - Overview of all namespaces containing public API elements
 * - Links to namespace pages for navigation
 * - Summary tables of classes and functions
 *
 * This is typically the first page generated during documentation generation.
 *
 * @see CodeIndex For the source of namespace and element data
 * @see ApiSummaryInput For the template input data
 */
class PublicApiSummaryWriter extends AbstractWriter
{
    /**
     * Creates a new API summary writer.
     *
     * @param $codeIndex The code index containing all elements
     * @param $filePath  The file path for the summary (e.g., '/readme')
     */
    public function __construct(public readonly CodeIndex $codeIndex, string $filePath)
    {
        $filePath = str_replace('.md', '', $filePath);
        $filePath .= '.md';

        $this->writePath = $filePath;
        parent::__construct();
    }

    /**
     * Generates the API summary content.
     */
    public function makeContent(): string
    {
        return $this->getBuildIndex();
    }

    /**
     * Renders the API summary using the api_summary template.
     *
     * @return string The generated Markdown content
     */
    public function getBuildIndex(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/api_summary.latte',
            params : new ApiSummaryInput(
                codeIndex: $this->codeIndex
            ),
        );
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\{ApiSummaryPage, CodeIndex};

/**
 * Template input for the main API summary page.
 *
 * Prepares namespace data for the api_summary.latte template, filtering
 * to only include namespaces that contain public API elements.
 *
 * @see PublicApiSummaryWriter For where this input is used
 */
class ApiSummaryInput
{
    /**
     * Namespaces containing API elements, keyed by namespace name.
     *
     * @var array<string, NamespacePageInput>
     */
    public readonly array $namespaces;

    /**
     * The summary page wrapper for URL linking.
     */
    public readonly ApiSummaryPage $summaryPage;

    /**
     * Creates a new API summary input.
     *
     * Filters the code index to only include namespaces with API elements.
     *
     * @param $codeIndex The code index containing all elements
     */
    public function __construct(
        CodeIndex $codeIndex,
    ) {
        $this->summaryPage = new ApiSummaryPage;

        $namespaces = [];

        // Filter namespaces to only include those with API classes
        foreach ($codeIndex->namespaces as $namespaceWrapper) {
            if (!empty($namespaceWrapper->apiElements)) {
                $namespaces[$namespaceWrapper->namespace] = new NamespacePageInput($namespaceWrapper);
            }
        }

        $this->namespaces = $namespaces;
    }
}

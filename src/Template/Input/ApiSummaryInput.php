<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\{ApiSummaryPage, CodeIndex};

class ApiSummaryInput
{
    /**
     * @var array<string, NamespacePageInput>
     */
    public readonly array $namespaces;

    public readonly ApiSummaryPage $summaryPage;

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

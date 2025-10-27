<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Template\Input;

use JulienBoudry\PhpReference\ApiSummaryPage;
use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Reflect\{ClassWrapper, NamespaceWrapper};

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
            $apiClasses = array_filter(
                $namespaceWrapper->classes,
                fn(ClassWrapper $class) => $class->willBeInPublicApi
            );

            if (!empty($apiClasses)) {
                // Create a filtered NamespaceWrapper with only API classes
                $filteredNamespace = new NamespaceWrapper(
                    namespace: $namespaceWrapper->namespace,
                    classes: $apiClasses
                );

                // Reuse NamespacePageInput to organize classes by type
                $namespaces[$namespaceWrapper->namespace] = new NamespacePageInput($filteredNamespace);
            }
        }

        $this->namespaces = $namespaces;
    }
}

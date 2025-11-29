<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\NamespaceWrapper;
use JulienBoudry\PhpReference\Template\Input\NamespacePageInput;

/**
 * Writer for generating namespace index documentation pages.
 *
 * This writer creates an index page for each namespace, listing all the
 * classes, interfaces, traits, enums, and functions within that namespace.
 * The generated page provides a navigational entry point to the elements
 * contained in the namespace.
 *
 * The output path follows the namespace hierarchy, e.g.:
 * - MyApp\Domain -> /ref/MyApp/Domain/readme.md
 *
 * @see NamespaceWrapper For the namespace data source
 * @see NamespacePageInput For the template input data
 */
class NamespacePageWriter extends AbstractWriter
{
    /**
     * Creates a new namespace page writer.
     *
     * @param $namespaceWrapper The namespace wrapper to generate documentation for
     * @param $indexFileName    The name for the index file (default: 'readme')
     */
    public function __construct(
        public readonly NamespaceWrapper $namespaceWrapper,
        string $indexFileName = 'readme',
    ) {
        // Generate path like /ref/Namespace/Name/readme.md
        $namespacePath = str_replace('\\', '/', $this->namespaceWrapper->namespace);
        $this->writePath = '/ref/' . $namespacePath . '/' . $indexFileName . '.md';

        parent::__construct();
    }

    /**
     * Generates the namespace index content using the namespace_page template.
     */
    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/namespace_page.latte',
            params : new NamespacePageInput(
                namespaceWrapper: $this->namespaceWrapper
            ),
        );
    }
}

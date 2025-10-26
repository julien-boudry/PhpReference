<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Reflect\NamespaceWrapper;
use JulienBoudry\PhpReference\Template\Input\NamespacePageInput;

class NamespacePageWriter extends AbstractWriter
{
    public function __construct(
        public readonly NamespaceWrapper $namespace,
        string $indexFileName = 'readme',
    ) {
        // Generate path like /ref/Namespace/Name/readme.md
        $namespacePath = str_replace('\\', '/', $this->namespace->namespace);
        $this->writePath = '/ref/' . $namespacePath . '/' . $indexFileName . '.md';

        parent::__construct();
    }

    public function makeContent(): string
    {
        return self::$latte->renderToString(
            name: AbstractWriter::TEMPLATE_DIR . '/namespace_page.latte',
            params : new NamespacePageInput(
                namespace: $this->namespace
            ),
        );
    }
}

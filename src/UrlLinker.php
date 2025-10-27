<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;

class UrlLinker
{
    public function __construct(
        public readonly WritableInterface $sourcePage,
    ) {}

    public function to(WritableInterface $page): string
    {
        $sourceDirectory = $this->sourcePage->getPageDirectory();
        $destinationPath = $page->getPagePath();

        // Calculate relative path from source to destination
        return $this->getRelativePath($sourceDirectory, $destinationPath);

        // Return the markdown link format
    }

    protected function getRelativePath(string $from, string $to): string
    {
        $from = rtrim($from, '/');
        $to = ltrim($to, '/');

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Add '../' for each remaining source directory level
        $relativePath = str_repeat('../', \count($fromParts) - 1);

        // Add the remaining destination path
        $relativePath .= implode('/', $toParts);

        return $relativePath;
    }
}

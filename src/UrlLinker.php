<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;

class UrlLinker
{
    public function __construct(
        public readonly WritableInterface&ReflectionWrapper $sourcePage,
    ) {}

    public function to(WritableInterface&ReflectionWrapper $page): string
    {
        $sourceDirectory = $this->sourcePage->getPageDirectory();
        $destinationPath = $page->getPagePath();

        // Calculate relative path from source to destination
        $relativePath = $this->getRelativePath($sourceDirectory, $destinationPath);

        // Return the markdown link format
        return $relativePath;
    }

    protected function getRelativePath(string $from, string $to): string
    {
        $from = rtrim($from, '/');
        $to = ltrim($to, '/');

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Add '../' for each remaining source directory level
        $relativePath = str_repeat('../', count($fromParts) - 1);

        // Add the remaining destination path
        $relativePath .= implode('/', $toParts);

        return $relativePath;
    }
}
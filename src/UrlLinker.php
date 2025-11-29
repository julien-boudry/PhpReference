<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;

/**
 * Generates relative URLs between documentation pages.
 *
 * When generating documentation with multiple interconnected pages, each page needs
 * to link to other pages using relative paths. This class calculates those relative
 * paths based on the source page's location and the destination page's location.
 *
 * The UrlLinker is typically instantiated with a source page (the page containing
 * the links) and then used to generate links to various destination pages.
 *
 * @see WritableInterface For the contract that pages must implement
 */
class UrlLinker
{
    /**
     * Creates a new UrlLinker for generating links from the source page.
     *
     * @param WritableInterface $sourcePage The page from which links will be generated
     */
    public function __construct(
        public readonly WritableInterface $sourcePage,
    ) {}

    /**
     * Generates a relative URL from the source page to a destination page.
     *
     * This method calculates the relative path needed to link from the current source page
     * (set in constructor) to any other page in the documentation. It handles different
     * directory structures automatically, ensuring links work correctly regardless of where
     * files are located in the output directory tree.
     *
     * @param WritableInterface $page The destination page to link to
     *
     * @return string The relative path from source to destination (e.g., '../ClassB/ClassB.md')
     *
     * @example
     * // Linking from /ref/Namespace/ClassA/ to /ref/Namespace/ClassB/ClassB.md
     * $linker = new UrlLinker($classA);
     * $link = $linker->to($classB); // Returns: '../ClassB/ClassB.md'
     */
    public function to(WritableInterface $page): string
    {
        // Get the directory where the source page is located
        // This is where we're linking FROM (e.g., '/ref/Namespace/ClassA')
        $sourceDirectory = $this->sourcePage->getPageDirectory();

        // Get the full path of the destination page file
        // This is where we're linking TO (e.g., '/ref/Namespace/ClassB/ClassB.md')
        $destinationPath = $page->getPagePath();

        // Calculate and return the relative path from source directory to destination file
        return $this->getRelativePath($sourceDirectory, $destinationPath);
    }

    /**
     * Calculates the relative path from a source directory to a destination file.
     *
     * This method computes the relative path needed to navigate from a source directory
     * to a destination file, handling common path prefixes and edge cases.
     *
     * Algorithm:
     * 1. Normalize paths by removing leading/trailing slashes
     * 2. Handle the special case of root directory as source
     * 3. Find the common path prefix between source and destination
     * 4. Calculate how many '../' are needed to reach the common ancestor
     * 5. Append the remaining path to the destination
     *
     * @param $from The source directory path (where we're linking FROM)
     * @param $to   The destination file path (where we're linking TO)
     *
     * @return string The relative path with appropriate '../' prefixes
     *
     * @example
     * // Same directory: '/ref/Namespace' -> '/ref/Namespace/file.md' = 'file.md'
     * // Parent directory: '/ref/Namespace/Class' -> '/ref/file.md' = '../../file.md'
     * // Sibling: '/ref/ClassA' -> '/ref/ClassB/file.md' = '../ClassB/file.md'
     */
    protected function getRelativePath(string $from, string $to): string
    {
        // Step 1: Normalize paths by removing leading and trailing slashes
        // This ensures consistent path comparison regardless of input format
        $from = trim($from, '/');
        $to = trim($to, '/');

        // Step 2: Handle edge case where source is in the root directory
        // If the source directory is empty (root), we can directly return the destination path
        if ($from === '') {
            return $to;
        }

        // Step 3: Split paths into directory/file components
        // $fromParts contains only directories (source is always a directory)
        // $toParts contains directories + the final filename
        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Step 4: Find the common path prefix between source and destination
        // We compare directories only, excluding the final filename from $toParts
        // Example: '/ref/Namespace' and '/ref/Namespace/Class/file.md' share 'ref/Namespace'
        $commonLength = 0;
        $minLength = min(\count($fromParts), \count($toParts) - 1); // -1 because last part of $to is the filename

        for ($i = 0; $i < $minLength; $i++) {
            if ($fromParts[$i] === $toParts[$i]) {
                $commonLength++;
            } else {
                // Stop at the first difference
                break;
            }
        }

        // Step 5: Check if source and destination are in the exact same directory
        // If all source directories match all destination directories (excluding filename),
        // we can simply return the filename without any '../' navigation
        if ($commonLength === \count($fromParts) && $commonLength === \count($toParts) - 1) {
            return $toParts[\count($toParts) - 1];
        }

        // Step 6: Calculate how many levels we need to go up from the source directory
        // We need one '../' for each directory in $from that's not in the common path
        // Example: from '/ref/Namespace/Class' with common '/ref' needs 2x '../'
        $upLevels = \count($fromParts) - $commonLength;

        // Step 7: Build the relative path
        // Start with the appropriate number of '../' to reach the common ancestor
        $relativePath = str_repeat('../', $upLevels);

        // Step 8: Append the remaining destination path parts after the common ancestor
        // This includes all directories and the filename after the common prefix
        $remainingParts = \array_slice($toParts, $commonLength);
        $relativePath .= implode('/', $remainingParts);

        return $relativePath;
    }
}

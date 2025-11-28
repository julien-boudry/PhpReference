<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * This is the summary line for the class.
 *
 * This is the first paragraph of the description.
 * It can span multiple lines.
 *
 * This is the second paragraph of the description.
 * It also spans multiple lines.
 *
 * @api
 */
class DocBlockFixture
{
    /**
     * Summary only, no description.
     */
    public string $summaryOnly = 'value';

    /**
     * Summary line for the property.
     *
     * Description paragraph for the property.
     */
    public string $summaryAndDescription = 'value';

    /**
     * First line summary.
     *
     * Second paragraph is the description.
     *
     * Third paragraph continues the description.
     */
    public function methodWithMultipleParagraphs(): void {}

    /**
     * Summary for method with params.
     *
     * Description explains what this method does in detail.
     *
     * @param string $foo The foo parameter description.
     * @param int $bar The bar parameter description.
     *
     * @return bool Returns true on success.
     */
    public function methodWithParams(string $foo, int $bar): bool
    {
        return true;
    }

    /**
     * @param string $value A value
     */
    public function methodWithOnlyTags(string $value): void {}

    /**
     * Summary with special characters: `code`, *italic*, **bold**.
     *
     * Description with special characters:
     * - List item 1
     * - List item 2
     *
     * Another paragraph with `inline code`.
     */
    public function methodWithMarkdown(): void {}
}

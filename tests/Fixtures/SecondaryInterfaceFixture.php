<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A secondary interface for testing multiple interface implementation.
 *
 * @api
 */
interface SecondaryInterfaceFixture
{
    /**
     * Secondary interface constant.
     */
    public const string SECONDARY_CONST = 'secondary';

    /**
     * Process some data.
     *
     * @param array<string, mixed> $data The data to process.
     *
     * @return array<string, mixed> The processed data.
     */
    public function process(array $data): array;
}

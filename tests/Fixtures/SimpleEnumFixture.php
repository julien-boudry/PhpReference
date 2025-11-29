<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A simple enum without backing type.
 *
 * @api
 */
enum SimpleEnumFixture
{
    case First;
    case Second;
    case Third;
}

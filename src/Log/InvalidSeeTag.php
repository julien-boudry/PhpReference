<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

class InvalidSeeTag extends InvalidTag
{
    protected string $tagName = '@see';
}
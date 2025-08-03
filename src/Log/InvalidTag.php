<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

abstract class InvalidTag extends PhpDocParsingException
{
    abstract protected string $tagName { get; }

    public function __construct(
        string $message = 'Invalid tag',
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message: \sprintf('Tag "%s" is invalid: %s', $this->tagName, $message),
            previous: $previous
        );
    }
}

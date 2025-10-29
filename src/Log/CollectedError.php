<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

readonly class CollectedError
{
    public function __construct(
        public string $message,
        public ErrorLevel $level,
        public ?string $context = null,
        public ?string $elementName = null,
        public ?\Throwable $exception = null,
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable,
    ) {}
}

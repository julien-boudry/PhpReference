<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

/**
 * Immutable data object representing a collected error or warning.
 *
 * This class stores all information about a non-fatal issue that occurred
 * during documentation generation. The data is used for reporting at the
 * end of the generation process.
 *
 * @see ErrorCollector For how errors are collected and managed
 * @see ErrorLevel For the severity levels
 */
readonly class CollectedError
{
    /**
     * Creates a new collected error instance.
     *
     * @param $message     Human-readable error message
     * @param $level       Severity level of the error
     * @param $context     Additional context about where/why the error occurred
     * @param $elementName Name of the element being processed when error occurred
     * @param $exception   The underlying exception if any
     * @param $timestamp   When the error was collected
     */
    public function __construct(
        public string $message,
        public ErrorLevel $level,
        public ?string $context = null,
        public ?string $elementName = null,
        public ?\Throwable $exception = null,
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable,
    ) {}
}

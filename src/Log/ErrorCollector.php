<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

/**
 * Centralized collector for non-fatal errors and warnings during documentation generation.
 *
 * During documentation generation, various issues may occur that are not fatal
 * (e.g., unresolvable @see references, invalid PHPDoc tags). Rather than stopping
 * the process, these issues are collected and reported at the end.
 *
 * The collector categorizes errors by severity level and provides methods for:
 * - Adding errors with context and source element information
 * - Retrieving errors filtered by level
 * - Generating formatted reports for console output
 * - Summarizing error counts by level
 *
 * @see ErrorLevel For the severity levels
 * @see CollectedError For the error data structure
 */
class ErrorCollector
{
    /**
     * All collected errors.
     *
     * @var array<int, CollectedError>
     */
    private array $errors = [];

    /**
     * Error counts grouped by severity level.
     *
     * @var array<string, int>
     */
    private array $errorCounts = [];

    /**
     * Adds an error to the collection.
     *
     * @param $message   Human-readable error message
     * @param $level     Severity level of the error
     * @param $context   Additional context about where/why the error occurred
     * @param $element The element being processed when the error occurred
     * @param $exception The underlying exception if any
     */
    public function addError(
        string $message,
        ErrorLevel $level = ErrorLevel::WARNING,
        ?string $context = null,
        ?ReflectionWrapper $element = null,
        ?\Throwable $exception = null
    ): void {
        $error = new CollectedError(
            message: $message,
            level: $level,
            context: $context,
            elementName: $element?->name,
            exception: $exception,
            timestamp: new \DateTimeImmutable,
        );

        $this->errors[] = $error;

        // Count errors by type
        $key = $level->value;
        $this->errorCounts[$key] = ($this->errorCounts[$key] ?? 0) + 1;
    }

    /**
     * Adds a warning-level error.
     *
     * Convenience method for the most common error type.
     *
     * @param $message Human-readable warning message
     * @param $context Additional context
     * @param $element The element being processed
     */
    public function addWarning(string $message, ?string $context = null, ?ReflectionWrapper $element = null): void
    {
        $this->addError($message, ErrorLevel::WARNING, $context, $element);
    }

    /**
     * Adds a notice-level error.
     *
     * Notices are less severe than warnings and typically informational.
     *
     * @param $message Human-readable notice message
     * @param $context Additional context
     * @param $element The element being processed
     */
    public function addNotice(string $message, ?string $context = null, ?ReflectionWrapper $element = null): void
    {
        $this->addError($message, ErrorLevel::NOTICE, $context, $element);
    }

    /**
     * Retrieves collected errors, optionally filtered by level.
     *
     * @param $filterByLevel Only return errors of this level, or null for all
     *
     * @return array<int, CollectedError>
     */
    public function getErrors(?ErrorLevel $filterByLevel = null): array
    {
        if ($filterByLevel === null) {
            return $this->errors;
        }

        return array_values(array_filter(
            $this->errors,
            fn(CollectedError $error) => $error->level === $filterByLevel
        ));
    }

    /**
     * Checks if any errors have been collected.
     *
     * @param $level Check only for errors of this level, or null for any
     *
     * @return bool True if errors exist
     */
    public function hasErrors(?ErrorLevel $level = null): bool
    {
        if ($level === null) {
            return !empty($this->errors);
        }

        return !empty($this->getErrors($level));
    }

    /**
     * Returns the count of collected errors.
     *
     * @param $level Count only errors of this level, or null for total
     */
    public function getErrorCount(?ErrorLevel $level = null): int
    {
        if ($level === null) {
            return \count($this->errors);
        }

        return $this->errorCounts[$level->value] ?? 0;
    }

    /**
     * Returns a summary of error counts grouped by level.
     *
     * @return array<string, int> Level name => count
     */
    public function getSummary(): array
    {
        return $this->errorCounts;
    }

    /**
     * Clears all collected errors.
     *
     * Useful for resetting between documentation generation runs.
     */
    public function clear(): void
    {
        $this->errors = [];
        $this->errorCounts = [];
    }

    /**
     * Formats all collected errors for console output.
     *
     * Produces a human-readable report organized by error level,
     * including timestamps, element names, and context where available.
     *
     * @return string Formatted error report
     */
    public function formatForConsole(): string
    {
        if (empty($this->errors)) {
            return 'No errors or warnings collected.';
        }

        $output = "\n=== Error Report ===\n\n";

        foreach (ErrorLevel::cases() as $level) {
            $levelErrors = $this->getErrors($level);
            if (empty($levelErrors)) {
                continue;
            }

            $output .= \sprintf("%s (%d):\n", $level->getLabel(), \count($levelErrors));
            $output .= str_repeat('-', 50) . "\n";

            foreach ($levelErrors as $error) {
                $output .= \sprintf(
                    "  [%s] %s%s\n",
                    $error->timestamp->format('H:i:s'),
                    $error->elementName ? "[{$error->elementName}] " : '',
                    $error->message
                );

                if ($error->context) {
                    $output .= "    Context: {$error->context}\n";
                }

                if ($error->exception) {
                    $output .= "    Exception: {$error->exception->getMessage()}\n";
                }
            }

            $output .= "\n";
        }

        return $output;
    }
}

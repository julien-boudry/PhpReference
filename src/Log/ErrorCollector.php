<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

/**
 * Centralized error collector for non-fatal issues during documentation generation.
 * Allows collecting warnings and errors without stopping the process.
 */
class ErrorCollector
{
    /** @var array<int, CollectedError> */
    private array $errors = [];

    /** @var array<string, int> */
    private array $errorCounts = [];

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
            timestamp: new \DateTimeImmutable(),
        );

        $this->errors[] = $error;

        // Count errors by type
        $key = $level->value;
        $this->errorCounts[$key] = ($this->errorCounts[$key] ?? 0) + 1;
    }

    public function addWarning(string $message, ?string $context = null, ?ReflectionWrapper $element = null): void
    {
        $this->addError($message, ErrorLevel::WARNING, $context, $element);
    }

    public function addNotice(string $message, ?string $context = null, ?ReflectionWrapper $element = null): void
    {
        $this->addError($message, ErrorLevel::NOTICE, $context, $element);
    }

    /**
     * @return array<int, CollectedError>
     */
    public function getErrors(?ErrorLevel $filterByLevel = null): array
    {
        if ($filterByLevel === null) {
            return $this->errors;
        }

        return array_filter(
            $this->errors,
            fn(CollectedError $error) => $error->level === $filterByLevel
        );
    }

    public function hasErrors(?ErrorLevel $level = null): bool
    {
        if ($level === null) {
            return !empty($this->errors);
        }

        return !empty($this->getErrors($level));
    }

    public function getErrorCount(?ErrorLevel $level = null): int
    {
        if ($level === null) {
            return count($this->errors);
        }

        return $this->errorCounts[$level->value] ?? 0;
    }

    /**
     * Get a summary of all errors grouped by level.
     *
     * @return array<string, int>
     */
    public function getSummary(): array
    {
        return $this->errorCounts;
    }

    public function clear(): void
    {
        $this->errors = [];
        $this->errorCounts = [];
    }

    /**
     * Format errors for console output.
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

            $output .= sprintf("%s (%d):\n", $level->getLabel(), count($levelErrors));
            $output .= str_repeat('-', 50) . "\n";

            foreach ($levelErrors as $error) {
                $output .= sprintf(
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

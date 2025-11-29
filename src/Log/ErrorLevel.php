<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

/**
 * Severity levels for collected errors during documentation generation.
 *
 * Error levels are used to categorize issues by severity, allowing users to
 * focus on the most important problems first.
 *
 * @see ErrorCollector For how levels are used in error collection
 * @see CollectedError For the error data structure
 */
enum ErrorLevel: string
{
    /**
     * Informational notice - minor issues that may not require action.
     */
    case NOTICE = 'notice';

    /**
     * Warning - issues that should be addressed but don't prevent documentation.
     */
    case WARNING = 'warning';

    /**
     * Error - significant issues that may affect documentation quality.
     */
    case ERROR = 'error';

    /**
     * Returns a formatted label for console output.
     *
     * Includes an emoji indicator and uppercase name.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::NOTICE => '📝 NOTICES',
            self::WARNING => '⚠️  WARNINGS',
            self::ERROR => '❌ ERRORS',
        };
    }

    /**
     * Returns just the emoji indicator for this level.
     */
    public function getEmoji(): string
    {
        return match ($this) {
            self::NOTICE => '📝',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
        };
    }
}

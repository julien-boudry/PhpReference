<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Log;

enum ErrorLevel: string
{
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';

    public function getLabel(): string
    {
        return match ($this) {
            self::NOTICE => '📝 NOTICES',
            self::WARNING => '⚠️  WARNINGS',
            self::ERROR => '❌ ERRORS',
        };
    }

    public function getEmoji(): string
    {
        return match ($this) {
            self::NOTICE => '📝',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
        };
    }
}

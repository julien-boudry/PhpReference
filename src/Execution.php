<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\CodeIndex;

final class Execution
{
    public static self $instance;

    public function __construct (public readonly CodeIndex $codeIndex) {
        self::$instance = $this;
    }
}
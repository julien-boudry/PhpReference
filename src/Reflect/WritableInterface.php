<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

interface WritableInterface
{
    public function getPageDirectory(): string;
    public function getPagePath(): string;
}
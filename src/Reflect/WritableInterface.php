<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\UrlLinker;

interface WritableInterface
{
    public function getPageDirectory(): string;
    public function getPagePath(): string;
    public function getUrlLinker(): UrlLinker;
}
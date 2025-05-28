<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect\Capabilities;

use JulienBoudry\PhpReference\UrlLinker;

interface SignatureInterface
{
    public function getSignature(): string;
}
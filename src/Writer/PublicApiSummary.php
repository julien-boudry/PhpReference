<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Writer;

use JulienBoudry\PhpReference\Formater\ClassPublicApiSummaryFormater;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use SplFileObject;

class PublicApiSummary
{
    /** @var array<string, ClassPublicApiFormater> */
    public array $classformaters {
        get {
            $r = [];

            foreach ($this->codeIndex->getPublicClasses() as $className => $class) {
                $r[$className] = new ClassPublicApiSummaryFormater($class);
            }

            return $r;
        }
    }

    public function __construct(public readonly CodeIndex $codeIndex) {}

    public function getBuildIndex() : string
    {
        // TODO

        return '';
    }

    public function writeTo(SplFileObject $file): void
    {
        if (!$file->isWritable()) {
            throw new \RuntimeException("The file '{$file->getPathname()}' is not writable");
        }

        $file->fwrite($this->getBuildIndex());
    }
}
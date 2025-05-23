<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\Condorcet;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;

require_once __DIR__ . '/vendor/autoload.php';

// Recursively delete output directory
$outputPath = __DIR__ . '/output';
if (is_dir($outputPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }

    rmdir($outputPath);
}

$codeIndex = new CodeIndex(Condorcet::class);

$publicApiSummaryWriter = new PublicApiSummaryWriter($codeIndex);

foreach ($codeIndex->getPublicClasses() as $class) {
    new ClassPageWriter($class);

    foreach ($class->methods as $method) {
        new MethodPageWriter($method);
    }
}
<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\Condorcet;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Reflect\PropertyWrapper;
use JulienBoudry\PhpReference\Writer\AbstractWriter;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Writer\PropertyPageWriter;
use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;

require_once __DIR__ . '/vendor/autoload.php';

// Recursively delete output directory
AbstractWriter::getFlySystem()->deleteDirectory('/');

$codeIndex = new CodeIndex(Condorcet::class);

$publicApiSummaryWriter = new PublicApiSummaryWriter($codeIndex);

foreach ($codeIndex->getPublicClasses() as $class) {
    new ClassPageWriter($class);

    foreach ($class->methods as $method) {
        new MethodPageWriter($method);
    }

    foreach ($class->getAllApiProperties() as $property) {
        new PropertyPageWriter($property);
    }
}
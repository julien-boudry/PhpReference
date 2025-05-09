<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\Condorcet;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;

require_once __DIR__ . '/vendor/autoload.php';

$codeIndex = new CodeIndex(Condorcet::class);

$publicApiSummaryWriter = new PublicApiSummaryWriter($codeIndex);

foreach ($codeIndex->getPublicClasses() as $class) {
    new ClassPageWriter($class);
}
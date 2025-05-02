<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\Condorcet;
use JulienBoudry\PhpReference\Reflect\CodeIndex;
use JulienBoudry\PhpReference\Writer\PublicApiSummary;

require_once __DIR__ . '/vendor/autoload.php';

$codeIndex = new CodeIndex(Condorcet::class);

$publicApiSummary = new PublicApiSummary($codeIndex);

var_dump($publicApiSummary->classformaters);
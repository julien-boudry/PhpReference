<?php declare(strict_types=1);

use CondorcetPHP\Condorcet\Condorcet;
use JulienBoudry\PhpReference\Reflect\CodeIndex;

require_once __DIR__ . '/vendor/autoload.php';

$ci = new CodeIndex(Condorcet::class);


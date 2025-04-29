<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use Roave\BetterReflection\Reflection\ReflectionClass;

class CodeIndex
{
    /** @var string[] */
    public readonly array $classList;

    public function __construct()
    {
        // ClassFinder::disablePSR4Vendors(); // Optional; see performance notes below
        $classPathList = ClassFinder::getClassesInNamespace('CondorcetPHP\Condorcet', ClassFinder::RECURSIVE_MODE);

        $classList = [];

        foreach ($classPathList as $classPath) {
            var_dump($classPath);
            $classList[$classPath] = ReflectionClass::createFromName($classPath);
        }

        $this->classList = $classList;
    }
}
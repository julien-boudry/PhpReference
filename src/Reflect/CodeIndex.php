<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionMethod;

class CodeIndex
{
    /** @var array<string, ClassWrapper> */
    public readonly array $classList;

    public function __construct()
    {
        // ClassFinder::disablePSR4Vendors(); // Optional; see performance notes below
        $classPathList = ClassFinder::getClassesInNamespace('CondorcetPHP\Condorcet', ClassFinder::RECURSIVE_MODE);

        $classList = [];

        foreach ($classPathList as $classPath) {
            // $astLocator = (new BetterReflection())->astLocator();
            // $reflector = new DefaultReflector(new ComposerSourceLocator($classLoader, $astLocator));
            // $classList[$classPath] = $reflector->reflectClass($classPath);

            $classList[$classPath] = new ClassWrapper($classPath);
        }

        $this->classList = $classList;
    }

    /**
     * @return array<string, ClassWrapper>
     */
    public function getPublicClasses(): array
    {
        return array_filter($this->classList, function (ClassWrapper $class): bool {
            return $class->willBeInPublicApi;
        });
    }
}
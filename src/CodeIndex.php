<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionMethod;

class CodeIndex
{
    /** @var array<string, ClassWrapper> */
    public readonly array $classList;

    public function __construct(
        public readonly string $namespace,
    )
    {
        // ClassFinder::disablePSR4Vendors();
        $classPathList = ClassFinder::getClassesInNamespace($this->namespace, ClassFinder::RECURSIVE_MODE);

        $classList = [];

        foreach ($classPathList as $classPath) {
            $classList[$classPath] = new ClassWrapper($classPath);
        }

        $this->classList = $classList;
    }

    /**
     * @return array<string, ClassWrapper>
     */
    public function getApiClasses(): array
    {
        return array_filter($this->classList, function (ClassWrapper $class): bool {
            return $class->willBeInPublicApi;
        });
    }

    /**
     * @return array<string, ClassWrapper>
     */
    public function getPublicClasses(): array
    {
        return array_filter($this->classList, function (ClassWrapper $class): bool {
            return $class->hasPublicElements;
        });
    }
}
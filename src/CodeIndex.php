<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\EnumWrapper;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;

class CodeIndex
{
    /** @var array<string, ClassWrapper> */
    public readonly array $classList;

    public function __construct(
        public readonly string $namespace,
    )
    {
        $classPathList = ClassFinder::getClassesInNamespace($this->namespace, ClassFinder::RECURSIVE_MODE);

        $classList = [];

        foreach ($classPathList as $classPath) {
            $reflection = new ReflectionClass($classPath);

            if ($reflection->isEnum()) {
                $reflection = new ReflectionEnum($classPath);
                $classList[$classPath] = new EnumWrapper($reflection);
            } else {
                $classList[$classPath] = new ClassWrapper($reflection);
            }
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
}
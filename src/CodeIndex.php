<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, EnumWrapper};
use LogicException;
use ReflectionClass;
use ReflectionEnum;

class CodeIndex
{
    /** @var array<string, ClassWrapper> */
    public readonly array $classList;

    public function __construct(
        public readonly string $namespace,
    ) {
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

    public function getClassWrapper(string $className): ?ClassWrapper
    {
        return $this->classList[$className] ?? null;
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

    public function getElement(string $path): ClassElementWrapper
    {
        // If it's a class reference, resolve it to a ClassWrapper
        [$classPath, $elementName] = explode('::', $path);

        // Remove leading backslash if it's the first character
        if (str_starts_with($classPath, '\\')) {
            $classPath = substr($classPath, 1);
        }

        $class = $this->classList[$classPath] ?? null;

        if ($class === null) {
            throw new LogicException("Class `{$classPath}` not found");
        }

        $element = $class->getElementByName(str_replace('()', '', $elementName));

        if ($element === null) {
            throw new LogicException("Element `{$elementName}` not found in class `{$classPath}` for see tag.");
        }

        return $element;
    }
}

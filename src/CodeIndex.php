<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, EnumWrapper, ReflectionWrapper};
use LogicException;
use ReflectionClass;
use ReflectionEnum;
use JulienBoudry\PhpReference\Reflect\NamespaceWrapper;

class CodeIndex
{
    /** @var array<string, NamespaceWrapper> */
    public protected(set) array $namespaces = [];

    /**
     * @var array<string, ClassWrapper>
     */
    public array $elementsList {
        get {
            $result = [];
            foreach ($this->namespaces as $namespaceItem) {
                $result = array_merge($result, $namespaceItem->classes);
            }

            return $result;
        }
    }

    public function __construct(
        public readonly string $namespace,
    ) {
        $classPathList = ClassFinder::getClassesInNamespace($this->namespace, ClassFinder::RECURSIVE_MODE);

        /**
         * @var array<string, array<string, ReflectionWrapper>>
         */
        $namespaceGroups = [];

        foreach ($classPathList as $classPath) {
            $reflection = new ReflectionClass($classPath);

            $classWrapper = $reflection->isEnum()
                ? new EnumWrapper(new ReflectionEnum($classPath))
                : new ClassWrapper($reflection);

            // Extraire le namespace de la classe
            $elementNamespace = $reflection->getNamespaceName();

            // Grouper par namespace
            if (!isset($namespaceGroups[$elementNamespace])) {
                $namespaceGroups[$elementNamespace] = [];
            }
            $namespaceGroups[$elementNamespace][$classPath] = $classWrapper;
        }

        ksort($namespaceGroups, SORT_STRING);

        // Créer les objets NamespaceWrapper
        foreach ($namespaceGroups as $namespaceName => $namespaceElements) {
            $namespaceWrapper = new NamespaceWrapper($namespaceName, $namespaceElements);
            $this->namespaces[$namespaceName] = $namespaceWrapper;

            foreach ($namespaceElements as $reflectionWrapper) {
                $reflectionWrapper->declaringNamespace = $namespaceWrapper;
            }
        }
    }

    public function getClassWrapper(string $className): ?ClassWrapper
    {
        return $this->elementsList[$className] ?? null;
    }

    /**
     * @return array<string, ClassWrapper>
     */
    public function getApiClasses(): array
    {
        return array_filter($this->elementsList, function (ClassWrapper $class): bool {
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

        $class = $this->elementsList[$classPath] ?? null;

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

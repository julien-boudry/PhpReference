<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Exception\UnresolvableReferenceException;
use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, EnumWrapper, ReflectionWrapper};
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

        // Sort Namespace
        ksort($namespaceGroups, \SORT_STRING);

        // Créer les objets NamespaceWrapper
        foreach ($namespaceGroups as $namespaceName => $namespaceElements) {
            $namespaceWrapper = new NamespaceWrapper($namespaceName, $namespaceElements);
            $this->namespaces[$namespaceName] = $namespaceWrapper;

            foreach ($namespaceElements as $reflectionWrapper) {
                $reflectionWrapper->declaringNamespace = $namespaceWrapper;
            }
        }

        // Write hierarchy
        $this->createNamespaceHierarchy();
    }

    protected function createNamespaceHierarchy(): void
    {
        foreach ($this->namespaces as $namespaceWrapper) {
            $hierarchy = [];
            $parts = explode('\\', $namespaceWrapper->namespace);

            // Construire progressivement chaque niveau de namespace parent
            for ($i = 1; $i < \count($parts); $i++) {
                $parentNamespace = implode('\\', \array_slice($parts, 0, $i));

                // Si le parent existe dans notre index, l'ajouter à la hiérarchie
                // Sinon, ajouter le nom du namespace comme string
                $hierarchy[] = $this->namespaces[$parentNamespace] ?? $parentNamespace;
            }

            $namespaceWrapper->setHierarchy($hierarchy);
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
        // Validate path format
        if (!str_contains($path, '::')) {
            throw new UnresolvableReferenceException(
                reference: $path,
                message: "Invalid element path format '{$path}'. Expected format: 'ClassName::elementName'",
            );
        }

        // If it's a class reference, resolve it to a ClassWrapper
        [$classPath, $elementName] = explode('::', $path);

        // Remove leading backslash if it's the first character
        if (str_starts_with($classPath, '\\')) {
            $classPath = substr($classPath, 1);
        }

        $class = $this->elementsList[$classPath] ?? null;

        if ($class === null) {
            throw new UnresolvableReferenceException(
                reference: $classPath,
                message: "Class `{$classPath}` not found in indexed namespace",
            );
        }

        $element = $class->getElementByName(str_replace('()', '', $elementName));

        if ($element === null) {
            throw new UnresolvableReferenceException(
                reference: $path,
                message: "Element `{$elementName}` not found in class `{$classPath}`",
            );
        }

        return $element;
    }
}

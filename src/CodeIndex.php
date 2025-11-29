<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Exception\UnresolvableReferenceException;
use JulienBoudry\PhpReference\Reflect\{ClassElementWrapper, ClassWrapper, EnumWrapper, FunctionWrapper, InterfaceWrapper, ReflectionWrapper, TraitWrapper};
use ReflectionClass;
use ReflectionEnum;
use ReflectionFunction;
use JulienBoudry\PhpReference\Reflect\NamespaceWrapper;

/**
 * Indexes all classes and functions within a PHP namespace for documentation generation.
 *
 * The CodeIndex is responsible for discovering and organizing all PHP elements
 * (classes, interfaces, traits, enums, and functions) within a given namespace.
 * It uses the ClassFinder library for class discovery and PHP Parser for function
 * discovery, then wraps each element in the appropriate wrapper class for
 * enhanced reflection capabilities.
 *
 * The index maintains several filtered views of the elements:
 * - All elements (classes + functions)
 * - API elements only (elements marked for public API)
 * - Organized by namespace hierarchy
 *
 * @see ClassWrapper For class/interface/trait/enum wrappers
 * @see FunctionWrapper For standalone function wrappers
 * @see NamespaceWrapper For namespace organization
 */
class CodeIndex
{
    /**
     * All namespaces discovered in the indexed namespace, keyed by namespace name.
     *
     * @var array<string, NamespaceWrapper>
     */
    public protected(set) array $namespaces = [];

    /**
     * Returns all elements (classes and functions) in the index.
     *
     * Elements are aggregated from all namespace wrappers.
     *
     * @var array<string, ClassWrapper|FunctionWrapper>
     */
    public array $elementsList {
        get {
            $result = [];
            foreach ($this->namespaces as $namespaceItem) {
                $result = array_merge($result, $namespaceItem->elements);
            }

            return $result;
        }
    }

    /**
     * Returns only elements that are part of the public API.
     *
     * Filters elements based on the configured PublicApiDefinitionInterface.
     *
     * @var array<ClassWrapper|FunctionWrapper>
     */
    public array $apiElementsList {
        get => array_filter(
            $this->elementsList,
            fn($element) => $element->willBeInPublicApi
        );
    }

    /**
     * Returns only classes that are part of the public API.
     *
     * @var array<ClassWrapper>
     */
    public array $apiClassesList {
        get => array_filter(
            $this->apiElementsList,
            fn($element) => $element instanceof ClassWrapper
        );
    }

    /**
     * Returns only functions that are part of the public API.
     *
     * @var array<FunctionWrapper>
     */
    public array $apiFunctionsList {
        get => array_filter(
            $this->apiElementsList,
            fn($element) => $element instanceof FunctionWrapper
        );
    }

    /**
     * Returns all standalone functions in the index.
     *
     * @var FunctionWrapper[]
     */
    public array $functionsList {
        get => array_filter(
            $this->elementsList,
            fn($element) => $element instanceof FunctionWrapper
        );
    }

    /**
     * Returns all classes (including interfaces, traits, enums) in the index.
     *
     * @var ClassWrapper[]
     */
    public array $classesList {
        get => array_filter(
            $this->elementsList,
            fn($element) => $element instanceof ClassWrapper
        );
    }

    /**
     * Creates a new code index for the specified namespace.
     *
     * This constructor performs the following operations:
     * 1. Discovers all classes using ClassFinder (recursive)
     * 2. Wraps each class in the appropriate wrapper (ClassWrapper, EnumWrapper, etc.)
     * 3. Discovers standalone functions using FunctionDiscovery
     * 4. Organizes elements by namespace into NamespaceWrapper objects
     * 5. Builds the namespace hierarchy for breadcrumb navigation
     *
     * @param string $namespace The root namespace to index (e.g., 'MyApp\\Domain')
     */
    public function __construct(
        public readonly string $namespace,
    ) {
        $classPathList = ClassFinder::getClassesInNamespace($this->namespace, ClassFinder::RECURSIVE_MODE);

        /**
         * Temporary storage for elements grouped by namespace.
         *
         * @var array<string, array<string, ReflectionWrapper>>
         */
        $namespaceGroups = [];

        foreach ($classPathList as $classPath) {
            $classReflection = new ReflectionClass($classPath);
            $elementNamespace = $classReflection->getNamespaceName();

            $classReflectionWrapper = match (true) {
                $classReflection->isEnum() => new EnumWrapper(new ReflectionEnum($classPath)),
                $classReflection->isInterface() => new InterfaceWrapper($classReflection),
                $classReflection->isTrait() => new TraitWrapper($classReflection),
                default => new ClassWrapper($classReflection)
            };

            // Grouper par namespace
            if (!isset($namespaceGroups[$elementNamespace])) {
                $namespaceGroups[$elementNamespace] = [];
            }

            $namespaceGroups[$elementNamespace][$classPath] = $classReflectionWrapper;
        }

        // Discover standalone functions
        $functionPathList = FunctionDiscovery::getFunctionsInNamespace($this->namespace);

        /** @var FunctionWrapper[] */
        $functionsList = [];

        foreach ($functionPathList as $functionPath) {
            try {
                $functionWrapper = new FunctionWrapper(new ReflectionFunction($functionPath));

                // Add to namespace groups
                $elementNamespace = $functionWrapper->reflection->getNamespaceName();
                if (!isset($namespaceGroups[$elementNamespace])) {
                    $namespaceGroups[$elementNamespace] = [];
                }
                $namespaceGroups[$elementNamespace][$functionPath] = $functionWrapper;
            } catch (\ReflectionException $e) {
                // Skip functions that can't be reflected
                continue;
            }
        }

        // Sort Namespace
        ksort($namespaceGroups, \SORT_STRING);

        // Créer les objets NamespaceWrapper
        foreach ($namespaceGroups as $namespaceName => $namespaceElements) {
            // Séparer les classes et les fonctions
            $classes = array_filter($namespaceElements, fn($element) => $element instanceof ClassWrapper);
            $functions = array_filter($namespaceElements, fn($element) => $element instanceof FunctionWrapper);

            $namespaceWrapper = new NamespaceWrapper(
                namespace: $namespaceName,
                classes: $classes,
                functions: $functions
            );

            $this->namespaces[$namespaceName] = $namespaceWrapper;

            foreach ($namespaceElements as $reflectionWrapper) {
                $reflectionWrapper->declaringNamespace = $namespaceWrapper;
            }
        }

        // Write hierarchy
        $this->createNamespaceHierarchy();
    }

    /**
     * Builds the namespace hierarchy for breadcrumb navigation.
     *
     * For each namespace, this method creates an array of parent namespaces
     * (either as NamespaceWrapper objects if they exist in the index, or as
     * plain strings if they don't). This enables breadcrumb navigation in
     * the generated documentation.
     */
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

    /**
     * Retrieves a class wrapper by its fully qualified class name.
     *
     * @param string $className The fully qualified class name to look up
     *
     * @return ClassWrapper|null The wrapper if found, null otherwise
     */
    public function getClassWrapper(string $className): ?ClassWrapper
    {
        return $this->classesList[$className] ?? null;
    }

    /**
     * Retrieves a class element (method, property, or constant) by its path.
     *
     * The path format is 'ClassName::elementName' where elementName can include
     * special suffixes like '()' for methods or '$' prefix for properties.
     *
     * @param string $path The element path (e.g., 'MyClass::myMethod()', 'MyClass::$property')
     *
     * @throws UnresolvableReferenceException If the path format is invalid or element not found
     */
    public function getClassElement(string $path): ClassElementWrapper
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

        $class = $this->getClassWrapper($classPath);

        if ($class === null) {
            throw new UnresolvableReferenceException(
                reference: $classPath,
                message: "Class `{$classPath}` not found in indexed namespace",
            );
        }

        // Normalize element name: remove () suffix for methods and $ prefix for properties
        $normalizedElementName = str_replace('()', '', $elementName);
        if (str_starts_with($normalizedElementName, '$')) {
            $normalizedElementName = substr($normalizedElementName, 1);
        }

        $element = $class->getElementByName($normalizedElementName);

        if ($element === null) {
            throw new UnresolvableReferenceException(
                reference: $path,
                message: "Element `{$elementName}` not found in class `{$classPath}`",
            );
        }

        return $element;
    }
}

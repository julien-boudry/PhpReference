<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\{SignatureInterface, WritableInterface};
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;

/**
 * Wrapper for PHP class, interface, and trait reflection with enhanced capabilities.
 *
 * This wrapper provides access to all class members (methods, properties, constants)
 * with filtering options and API visibility support. It also generates code signatures
 * suitable for documentation display.
 *
 * Extended by:
 * - EnumWrapper for PHP enums
 * - InterfaceWrapper for interfaces
 * - TraitWrapper for traits
 *
 * @see ReflectionWrapper For base functionality
 * @see WritableInterface For page generation capabilities
 */
class ClassWrapper extends ReflectionWrapper implements SignatureInterface, WritableInterface
{
    /**
     * The element type identifier for output paths.
     */
    public const string TYPE = 'class';

    /**
     * Indentation used in signature generation.
     */
    protected const string TAB = '    ';

    /**
     * The fully qualified class name.
     */
    public string $name {
        get => $this->reflection->name;
    }

    /**
     * The short class name (without namespace).
     */
    public string $shortName {
        get => $this->reflection->getShortName();
    }

    /**
     * All methods in this class, keyed by name.
     *
     * @var array<string, MethodWrapper>
     */
    public array $methods {
        get => $this->methods ??= ReflectionWrapper::toWrapper($this->reflection->getMethods(), $this);
    }

    /**
     * All properties in this class, keyed by name.
     *
     * @var array<string, PropertyWrapper>
     */
    public array $properties {
        get => $this->properties ??= ReflectionWrapper::toWrapper($this->reflection->getProperties(), $this);
    }

    /**
     * All constants in this class, keyed by name.
     *
     * @var array<string, ClassConstantWrapper>
     */
    public array $constants {
        get => $this->constants ??= ReflectionWrapper::toWrapper($this->reflection->getReflectionConstants(), $this);
    }

    /**
     * The underlying ReflectionClass.
     */
    public ReflectionClass $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Returns the directory for this class's documentation page.
     */
    public function getPageDirectory(): string
    {
        return str_replace('\\', '/', parent::getPageDirectory() . "/{$this->name}");
    }

    /**
     * Returns the full path for this class's documentation page.
     */
    public function getPagePath(): string
    {
        return $this->getPageDirectory() . '/' . static::TYPE . "_{$this->shortName}.md";
    }

    /**
     * Filters class elements by visibility, static status, and locality.
     *
     * @param $list      Elements to filter
     * @param $public    Include public elements
     * @param $protected Include protected elements
     * @param $private   Include private elements
     * @param $static    Include static elements
     * @param $nonStatic Include non-static elements
     * @param $local     Include elements declared in this class
     * @param $nonLocal  Include inherited elements
     *
     * @return array<string, ClassElementWrapper> Filtered and sorted elements
     */
    protected function filterReflection(
        array $list,
        bool $public = true,
        bool $protected = true,
        bool $private = true,
        bool $static = true,
        bool $nonStatic = true,
        bool $local = true,
        bool $nonLocal = true
    ): array {
        $filtered = array_filter(
            array: $list,
            callback: function (ClassElementWrapper $reflectionWrapper) use ($public, $protected, $private, $static, $nonStatic, $local, $nonLocal): bool {
                $reflection = $reflectionWrapper->reflection;

                if ($reflection instanceof ReflectionFunctionAbstract && ! $reflection->isUserDefined()) {
                    return false;
                }

                if ($reflection->isPublic() && ! $public) {
                    return false;
                }

                if ($reflection->isProtected() && ! $protected) {
                    return false;
                }

                if ($reflection->isPrivate() && ! $private) {
                    return false;
                }

                if (! ($reflection instanceof ReflectionClassConstant) && $reflection->isStatic() && ! $static) {
                    return false;
                }

                if (! ($reflection instanceof ReflectionClassConstant) && ! $reflection->isStatic() && ! $nonStatic) {
                    return false;
                }

                if (! $nonLocal && ! $reflectionWrapper->isLocalTo($this)) {
                    return false;
                }

                if (! $local && $reflectionWrapper->isLocalTo($this)) {
                    return false;
                }

                return true;
            }
        );

        uasort(
            array: $filtered,
            callback: function (ClassElementWrapper $a, ClassElementWrapper $b): int {
                // First sort by visibility: public, protected, private
                $visibilityOrder = function (ClassElementWrapper $element): int {
                    if ($element->reflection->isPublic()) {
                        return 1;
                    }
                    if ($element->reflection->isProtected()) {
                        return 2;
                    }
                    if ($element->reflection->isPrivate()) {
                        return 3;
                    }

                    return 4;
                };

                $aVisibility = $visibilityOrder($a);
                $bVisibility = $visibilityOrder($b);

                if ($aVisibility !== $bVisibility) {
                    return $aVisibility <=> $bVisibility;
                }

                if ($a instanceof PropertyWrapper && $b instanceof PropertyWrapper) {
                    if ($a->isVirtual() && ! $b->isVirtual()) {
                        return 1; // Virtual properties go last
                    }

                    if (! $a->isVirtual() && $b->isVirtual()) {
                        return -1; // Non-virtual properties go first
                    }
                }

                return strcasecmp($a->name, $b->name);
            }
        );

        return $filtered;
    }

    /**
     * Returns all user-defined methods with optional filtering.
     *
     * @return array<string, MethodWrapper>
     */
    public function getAllUserDefinedMethods(bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true, bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->methods,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static,
            nonStatic: $nonStatic,
            local: $local,
            nonLocal: $nonLocal,
        );
    }

    /**
     * Returns all properties with optional filtering.
     *
     * @return array<string, PropertyWrapper>
     */
    public function getAllProperties(bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true, bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->properties,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static,
            nonStatic: $nonStatic,
            local: $local,
            nonLocal: $nonLocal,
        );
    }

    /**
     * Returns all constants with optional filtering.
     *
     * @return array<string, ClassConstantWrapper>
     */
    public function getAllConstants(bool $public = true, bool $protected = true, bool $private = true, bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->constants,
            public: $public,
            protected: $protected,
            private: $private,
            local: $local,
            nonLocal: $nonLocal,
        );
    }

    /**
     * Finds a class element by name (method, property, or constant).
     */
    public function getElementByName(string $name): ?ClassElementWrapper
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        }

        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        if (isset($this->constants[$name])) {
            return $this->constants[$name];
        }

        return null;
    }

    /**
     * Filters elements to only those in the public API.
     *
     * @param $list Elements to filter
     *
     * @return array<string, ClassElementWrapper>
     */
    protected function filterApiReflection(array $list): array
    {
        return array_filter(
            array: $list,
            callback: function (ClassElementWrapper $reflectionWrapper): bool {
                return $reflectionWrapper->willBeInPublicApi;
            }
        );
    }

    /**
     * Returns all public API methods.
     *
     * @return array<string, MethodWrapper>
     */
    public function getAllApiMethods(bool $static = true, bool $nonStatic = true, bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllUserDefinedMethods(
            static: $static,
            nonStatic: $nonStatic,
            protected: false,
            private: false,
            local: $local,
            nonLocal: $nonLocal
        ));
    }

    /**
     * Returns all public API properties.
     *
     * @return array<string, PropertyWrapper>
     */
    public function getAllApiProperties(bool $static = true, bool $nonStatic = true, bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllProperties(
            static: $static,
            nonStatic: $nonStatic,
            protected: false,
            private: false,
            local: $local,
            nonLocal: $nonLocal
        ));
    }

    /**
     * Returns all public API constants.
     *
     * @return array<string, ClassConstantWrapper>
     */
    public function getAllApiConstants(bool $local = true, bool $nonLocal = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllConstants(
            protected: false,
            private: false,
            local: $local,
            nonLocal: $nonLocal
        ));
    }

    /**
     * Checks if this class is user-defined (not built-in).
     */
    public function isUserDefined(): bool
    {
        return $this->reflection->isUserDefined();
    }

    /**
     * Generates the full class signature including members.
     *
     * @param $onlyApi Whether to include only API elements in the signature
     */
    public function getSignature(bool $onlyApi = false): string
    {
        $signature = '';

        // Head
        $type = match (true) {
            $this->reflection->isInterface() => 'interface',
            $this->reflection->isTrait() => 'trait',
            $this->reflection->isEnum() => 'enum',
            default => 'class',
        };

        $headModifiers = $this->getModifierNames();

        if ($type !== 'class') {
            $headModifiers = str_replace('final', '', $headModifiers);
        }

        $head = "{$headModifiers} {$type} {$this->name}" . $this->getHeritageHeadSignature();
        $head = mb_trim($head);

        $signature = $head;
        $signature .= "\n{\n";

        $signature .= $this->getInsideClassSignature($onlyApi);

        // Close
        return $signature . "\n}";
    }

    /**
     * Generates the extends/implements portion of the signature.
     */
    protected function getHeritageHeadSignature(): string
    {
        $parentClass = $this->reflection->getParentClass();
        $extends = $parentClass ? ' extends ' . $parentClass->getName() : '';

        $interfacesNames = $this->reflection->getInterfaceNames();
        $implements = $interfacesNames ? ' implements ' . implode(', ', $interfacesNames) : '';

        return "{$extends}{$implements}";
    }

    /**
     * Generates the inside-class portion of the signature (members).
     *
     * @param $onlyApi Whether to include only API elements
     */
    protected function getInsideClassSignature(bool $onlyApi): string
    {
        $signature = '';

        // Const
        $localConsts = $onlyApi ? $this->getAllApiConstants(nonLocal: false) : $this->getAllConstants(nonLocal: false);

        if (! empty($localConsts)) {
            $signature .= self::TAB . "// Constants\n";
        }

        foreach ($localConsts as $constant) {
            $signature .= self::TAB . $constant->getSignature(withClassName: false) . ";\n";
        }

        $inheritedConsts = $onlyApi ? $this->getAllApiConstants(local: false) : $this->getAllConstants(local: false);

        if (! empty($inheritedConsts)) {
            $signature .= self::TAB . "// Inherited Constants\n";
        }

        foreach ($inheritedConsts as $constant) {
            $signature .= self::TAB . $constant->getSignature(withClassName: true) . ";\n";
        }

        // Static Properties
        $localProperties = $onlyApi ? $this->getAllApiProperties(nonStatic: false, nonLocal: false) : $this->getAllProperties(nonStatic: false, nonLocal: false);

        if (! empty($localProperties)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Static Properties\n";
        }

        foreach ($localProperties as $property) {
            $signature .= self::TAB . $property->getSignature(withClassName: false) . ";\n";
        }

        $inheritedProperties = $onlyApi ? $this->getAllApiProperties(nonStatic: false, local: false) : $this->getAllProperties(nonStatic: false, local: false);

        if (! empty($inheritedProperties)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Static Inherited Properties\n";
        }

        foreach ($inheritedProperties as $property) {
            $signature .= self::TAB . $property->getSignature(withClassName: true) . ";\n";
        }

        // Properties
        $localProperties = $onlyApi ? $this->getAllApiProperties(static: false, nonLocal: false) : $this->getAllProperties(static: false, nonLocal: false);

        if (! empty($localProperties)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Properties\n";
        }

        foreach ($localProperties as $property) {
            $signature .= self::TAB . $property->getSignature(withClassName: false) . ";\n";
        }

        $inheritedProperties = $onlyApi ? $this->getAllApiProperties(static: false, local: false) : $this->getAllProperties(static: false, local: false);

        if (! empty($inheritedProperties)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Inherited Properties\n";
        }

        foreach ($inheritedProperties as $property) {
            $signature .= self::TAB . $property->getSignature(withClassName: true) . ";\n";
        }

        // Static Methods
        $localMethods = $onlyApi ? $this->getAllApiMethods(nonStatic: false) : $this->getAllUserDefinedMethods(nonStatic: false);

        if (! empty($localMethods)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Static Methods\n";
        }

        foreach ($localMethods as $method) {
            $signature .= self::TAB . $method->getSignature(withClassName: true) . ";\n";
        }

        // Methods
        $localMethods = $onlyApi ? $this->getAllApiMethods(static: false, nonLocal: false) : $this->getAllApiMethods(static: false, nonLocal: false);

        if (! empty($localMethods)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Methods\n";
        }

        foreach ($localMethods as $method) {
            $signature .= self::TAB . $method->getSignature(withClassName: true) . ";\n";
        }

        $inheritedMethods = $onlyApi ? $this->getAllApiMethods(static: false, local: false) : $this->getAllApiMethods(static: true, local: false);

        if (! empty($inheritedMethods)) {
            $signature .= "\n";
            $signature .= self::TAB . "// Inherited Methods\n";
        }

        foreach ($inheritedMethods as $method) {
            $signature .= self::TAB . $method->getSignature(withClassName: false) . ";\n";
        }

        return $signature;
    }
}

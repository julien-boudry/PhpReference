<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;
use Reflector;

class ClassWrapper extends ReflectionWrapper implements WritableInterface, SignatureInterface
{
    public readonly bool $willBeInPublicApi;
    public readonly bool $hasPublicElements;

    public string $name {
        get => $this->reflection->name;
    }

    public string $shortName {
        get => $this->reflection->getShortName();
    }

    /** @var array<string, MethodWrapper> */
    public array $methods {
        get => ReflectionWrapper::toWrapper($this->reflection->getMethods(), $this); // @phpstan-ignore return.type
    }

    /**
     * @var array<string, PropertyWrapper>
     */
    public array $properties {
        get => ReflectionWrapper::toWrapper($this->reflection->getProperties(), $this); // @phpstan-ignore return.type
    }

    /** @var array<string, ClassConstantWrapper> */
    public array $constants {
        get => ReflectionWrapper::toWrapper($this->reflection->getReflectionConstants(), $this); // @phpstan-ignore return.type
    }

    public ReflectionClass $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(public readonly string $classPath)
    {
        parent::__construct(new ReflectionClass($classPath));

        // Class Will Be In Public Api
        if ($this->hasInternalTag) {
            $this->willBeInPublicApi = false;
        } elseif ($this->hasApiTag) {
            $this->willBeInPublicApi = true;
        } else {
            if (!empty($this->getAllApiMethods()) || !empty($this->getAllApiConstants()) || !empty($this->getAllApiProperties())) {
                $this->willBeInPublicApi = true;
            }

            // TODO : property, const
            $this->willBeInPublicApi ??= false;
        }

        // Class Has Public Element
        if (
            !empty($this->getAllUserDefinedMethods(protected: false, private: false)) ||
            !empty($this->getAllConstants(protected: false, private: false)) ||
            !empty($this->getAllProperties(protected: false, private: false))
        ) {
            $this->hasPublicElements = true;
        } else {
            $this->hasPublicElements = false;
        }
    }

    public function getPageDirectory(): string
    {
        return str_replace('\\', '/', parent::getPageDirectory() . "/{$this->name}");
    }

    public function getPagePath(): string
    {
        return $this->getPageDirectory() . "/class_{$this->shortName}.md";
    }

    /**
     * @return array<string, ClassElementWrapper>
     */
    protected function filterReflection(array $list, bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true): array
    {
        $filtered = array_filter(
            array: $list,
            callback: function (MethodWrapper|PropertyWrapper|ClassConstantWrapper $reflectionWrapper) use ($public, $protected, $private, $static, $nonStatic) {
                $reflection = $reflectionWrapper->reflection;

                if ($reflection instanceof ReflectionFunctionAbstract && !$reflection->isUserDefined()) {
                    return false;
                }

                if ($reflection->isPublic() && !$public) {
                    return false;
                }

                if ($reflection->isProtected() && !$protected) {
                    return false;
                }

                if ($reflection->isPrivate() && !$private) {
                    return false;
                }

                if (!($reflection instanceof ReflectionClassConstant) && $reflection->isStatic() && !$static) {
                    return false;
                }

                if (!($reflection instanceof ReflectionClassConstant) && !$reflection->isStatic() && !$nonStatic) {
                    return false;
                }

                return true;
            }
        );

        uasort(
            array: $filtered,
            callback: function (MethodWrapper|PropertyWrapper|ClassConstantWrapper $a, MethodWrapper|PropertyWrapper|ClassConstantWrapper $b) {
                return strcasecmp($a->name, $b->name);
            }
        );

        return $filtered;
    }

    /**
     * @return array<string, MethodWrapper>
     */
    public function getAllUserDefinedMethods(bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->methods,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static,
            nonStatic: $nonStatic,
        );
    }

    /**
     * @return array<string, PropertyWrapper>
     */
    public function getAllProperties(bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->properties,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static,
            nonStatic: $nonStatic,
        );
    }

    /**
     * @return array<string, ClassConstantWrapper>
     */
    public function getAllConstants(bool $public = true, bool $protected = true, bool $private = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterReflection(
            list: $this->constants,
            public: $public,
            protected: $protected,
            private: $private
        );
    }

    /**
     * @return array<string, MethodWrapper|PropertyWrapper>
     */
    protected function filterApiReflection(array $list): array
    {
        return array_filter(
            array: $list,
            callback: function (MethodWrapper|PropertyWrapper|ClassConstantWrapper $reflectionWrapper) {
                return $reflectionWrapper->hasApiTag;
            }
        );
    }

    /**
     * @return array<string, MethodWrapper>
     */
    public function getAllApiMethods(bool $static = true, bool $nonStatic = true): array
    {
        return $this->filterApiReflection($this->getAllUserDefinedMethods(static: $static, nonStatic: $nonStatic, protected: false, private: false)); // @phpstan-ignore return.type
    }

    /**
     * @return array<string, PropertyWrapper>
     */
    public function getAllApiProperties(bool $static = true, bool $nonStatic = true): array
    {
        return $this->filterApiReflection($this->getAllProperties(static: $static, nonStatic: $nonStatic, protected: false, private: false)); // @phpstan-ignore return.type
    }

    /**
     * @return array<string, ClassConstantWrapper>
     */
    public function getAllApiConstants(): array
    {
        return $this->filterApiReflection($this->getAllConstants(protected: false, private: false)); // @phpstan-ignore return.type
    }

    public function getSignature(bool $onlyApi = false): string
    {
        $signature = '';

        // Head
        $type = $this->reflection->isInterface() ? 'interface' : ($this->reflection->isTrait() ? 'trait' : 'class');

        $parentClass = $this->reflection->getParentClass();
        $extends = $parentClass ? ' extends ' . $parentClass->getName() : '';

        $interfacesNames = $this->reflection->getInterfaceNames();
        $implements = $interfacesNames ? ' implements ' . implode(', ', $interfacesNames) : '';

        $head = "{$this->getModifierNames()} {$type} {$this->name}{$extends}{$implements}";

        $signature = $head;
        $signature .= "\n{\n";

        // Const
        $consts = $onlyApi ? $this->getAllApiConstants() : $this->getAllConstants();

        if (!empty($consts)) {
            $signature .= "    // Constants\n";
        }

        foreach ($consts as $constant) {
            $signature .= '    ' . $constant->getSignature() . ";\n";
        }

        // Static Properties
        $props = $onlyApi ? $this->getAllApiProperties(nonStatic: false) : $this->getAllProperties(nonStatic: false);

        if (!empty($props)) {
            $signature .= "\n";
            $signature .= "    // Static Properties\n";
        }

        foreach ($props as $property) {
            $signature .= '    ' . $property->getSignature() . ";\n";
        }

        // Properties
        $props = $onlyApi ? $this->getAllApiProperties(static: false) : $this->getAllProperties(static: false);

        if (!empty($props)) {
            $signature .= "\n";
            $signature .= "    // Properties\n";
        }

        foreach ($props as $property) {
            $signature .= '    ' . $property->getSignature() . ";\n";
        }

        // Static Methods
        $methods = $onlyApi ? $this->getAllApiMethods(nonStatic: false) : $this->getAllUserDefinedMethods();

        if (!empty($methods)) {
            $signature .= "\n";
            $signature .= "    // Methods\n";
        }

        foreach ($methods as $method) {
            $signature .= '    ' . $method->getSignature(forClassRepresentation: true) . ";\n";
        }

        // Methods
        $methods = $onlyApi ? $this->getAllApiMethods(static: false) : $this->getAllApiMethods(static: false);

        if (!empty($methods)) {
            $signature .= "\n";
            $signature .= "    // Methods\n";
        }

        foreach ($methods as $method) {
            $signature .= '    ' . $method->getSignature(forClassRepresentation: true) . ";\n";
        }

        // Close
        return $signature . "\n}";;
    }
}
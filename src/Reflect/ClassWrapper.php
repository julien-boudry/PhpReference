<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
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

class ClassWrapper extends ReflectionWrapper
{
    public readonly bool $willBeInPublicApi;

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

        // Class Will Be Public
        if ($this->hasInternalTag) {
            $this->willBeInPublicApi = false;
        } elseif ($this->hasApiTag) {
            $this->willBeInPublicApi = true;
        } else {
            foreach ($this->getAllUserDefinedMethods(protected: false, private: false) as $method) {
                if ($method->hasApiTag) {
                    $this->willBeInPublicApi = true;
                    break;
                }
            }

            // TODO : property, const
            $this->willBeInPublicApi ??= false;
        }
    }

    /**
     * @return array<string, ClassElementWrapper>
     */
    protected function filterReflection(array $list, bool $public = true, bool $protected = true, bool $private = true, bool $static = true, bool $nonStatic = true): array
    {
        return array_filter(
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
        if ($this->willBeInPublicApi === false) {
            return [];
        }

        return array_filter(
            array: $list,
            callback: function (MethodWrapper|PropertyWrapper|ClassConstantWrapper $reflectionWrapper) {
                return $reflectionWrapper->willBeInPublicApi;
            }
        );
    }

    /**
     * @return array<string, MethodWrapper>
     */
    public function getAllApiMethods(bool $static = true, bool $nonStatic = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllUserDefinedMethods(static: $static, nonStatic: $nonStatic, protected: false, private: false));
    }

    /**
     * @return array<string, PropertyWrapper>
     */
    public function getAllApiProperties(bool $static = true, bool $nonStatic = true): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllProperties(static: $static, nonStatic: $nonStatic, protected: false, private: false));
    }

    /**
     * @return array<string, ClassConstantWrapper>
     */
    public function getAllApiConstants(): array
    {
        // @phpstan-ignore return.type
        return $this->filterApiReflection($this->getAllConstants(protected: false, private: false));
    }
}
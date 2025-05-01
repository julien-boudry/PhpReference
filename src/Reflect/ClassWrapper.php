<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class ClassWrapper extends ReflectionWrapper
{
    public readonly bool $willBeInPublicApi;

    /** @var array<MethodWrapper> */
    public readonly array $methods;

    /** @var array<PropertyWrapper> */
    public readonly array $properties;

    /** @var array<ClassConstantWrapper> */
    public readonly array $constants;

    public function __construct(public readonly string $classPath)
    {
        parent::__construct(new ReflectionClass($classPath));

        $this->methods = ReflectionWrapper::toWrapper($this->reflection->getMethods(), $this);
        $this->properties = ReflectionWrapper::toWrapper($this->reflection->getProperties(), $this);
        $this->constants = ReflectionWrapper::toWrapper($this->reflection->getConstants(), $this);

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
     * @return array<MethodWrapper|PropertyWrapper>
     */
    protected function filterReflection(array $list, bool $public = true, bool $protected = true, bool $private = true, bool $static = true): array
    {
        return array_filter(
            array: $list,
            callback: function (MethodWrapper|PropertyWrapper|ClassConstantWrapper $reflectionWrapper) use ($public, $protected, $private, $static) {
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

                if ($reflection->isStatic() && !$static) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @return array<MethodWrapper>
     */
    public function getAllUserDefinedMethods(bool $public = true, bool $protected = true, bool $private = true, bool $static = true): array
    {
        return $this->filterReflection(
            list: $this->methods,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static
        );
    }

    /**
     * @return array<PropertyWrapper>
     */
    public function getAllProperties(bool $public = true, bool $protected = true, bool $private = true, bool $static = true): array
    {
        return $this->filterReflection(
            list: $this->properties,
            public: $public,
            protected: $protected,
            private: $private,
            static: $static
        );
    }

    /**
     * @return array<ClassConstantWrapper>
     */
    public function getAllConstants(bool $public = true, bool $protected = true, bool $private = true): array
    {
        return $this->filterReflection(
            list: $this->constants,
            public: $public,
            protected: $protected,
            private: $private
        );
    }

    /**
     * @return array<MethodWrapperr|PropertiesWrapper>
     */
    protected function filterApiReflection(array $list): array
    {
        if ($this->willBeInPublicApi === false) {
            return [];
        }

        return array_filter(
            array: $list,
            callback: function (MethodWrapper|PropertyWrapper $reflectionWrapper) {
                return $reflectionWrapper->willBeInPublicApi;
            }
        );
    }

    /**
     * @return array<MethodWrapper>
     */
    public function getAllApiMethods(bool $static = true): array
    {
        return $this->filterApiReflection($this->getAllUserDefinedMethods(static: $static, protected: false, private: false));
    }

    /**
     * @return array<PropertyWrapper>
     */
    public function getAllApiProperties(bool $static = true): array
    {
        return $this->filterApiReflection($this->getAllProperties(static: $static, protected: false, private: false));
    }

    /**
     * @return array<ClassConstantWrapper>
     */
    public function getAllApiConstants(): array
    {
        return $this->filterApiReflection($this->getAllConstants(protected: false, private: false));
    }
}
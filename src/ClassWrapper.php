<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use HaydenPierce\ClassFinder\ClassFinder;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;

class ClassWrapper
{
    public readonly ReflectionClass $reflectionClass;
    public readonly ?DocBlock $docBlock;

    public readonly bool $hasApiTag;
    public readonly bool $hasInternalTag;

    public readonly bool $classWillBePublic;

    /** @var array<ReflectionMethod> */
    public readonly array $methods;

    /** @var array<ReflectionProperties> */
    public readonly array $properties;

    public function __construct(public readonly string $classPath)
    {
        $this->reflectionClass = new ReflectionClass($classPath);
        $this->methods = $this->reflectionClass->getMethods();
        $this->properties = $this->reflectionClass->getProperties();

         // Docblock
        $docComment = $this->reflectionClass->getDocComment();
        $this->docBlock = !empty($docComment) ? Util::getDocBlocFactory()->create($docComment) : null;

        // Class docBlock visibility
        if ($this->docBlock !== null && $this->docBlock->hasTag('api')) {
            $this->hasApiTag = true;
        } else {
            $this->hasApiTag = false;
        }

        if ($this->docBlock !== null && $this->docBlock->hasTag('internal')) {
            $this->hasInternalTag = true;
        } else {
            $this->hasInternalTag = false;
        }

        // Class Will Be Public
        if ($this->hasInternalTag) {
            $this->classWillBePublic = false;
        } elseif ($this->hasApiTag) {
            $this->classWillBePublic = true;
        } else {
            foreach ($this->getAllUserDefinedMethods(protected: false, private: false) as $method) {
                $docBlockMethod = $method->getDocComment();

                if (!empty($docBlockMethod)) {
                    $docBlockMethod = Util::getDocBlocFactory()->create($docBlockMethod);

                    if ($docBlockMethod->hasTag('api')) {
                        $this->classWillBePublic = true;
                        break;
                    }
                }
            }

            // TODO : property, const

            $this->classWillBePublic ??= false;
        }
    }

    /**
     * @return array<ReflectionMethod|ReflectionProperty>
     */
    protected function filterReflection(array $list, bool $public = true, bool $protected = true, bool $private = true, bool $static = true): array
    {
        return array_filter(
            array: $this->methods,
            callback: function (ReflectionMethod|ReflectionProperty $reflection) use ($public, $protected, $private, $static) {
                if (!$reflection->isUserDefined()) {
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
     * @return array<ReflectionProperty>
     */
    public function getAllUserDefinedProperties(bool $public = true, bool $protected = true, bool $private = true, bool $static = true): array
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
     * @return array<ReflectionMethod>
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
     * @return array<ReflectionMethod|ReflectionProperty>
     */
    protected function filterApiReflection(array $list): array
    {
        if ($this->classWillBePublic === false) {
            return [];
        }

        return array_filter(
            array: $list,
            callback: function (ReflectionMethod|ReflectionProperty $reflection) {
                $docBlockMethod = $reflection->getDocComment();

                if (!empty($docBlockMethod)) {
                    $docBlockMethod = Util::getDocBlocFactory()->create($docBlockMethod);

                    if ($docBlockMethod->hasTag('api')) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * @return array<ReflectionMethod>
     */
    public function getAllApiMethods(bool $static = true): array
    {
        return $this->getAllUserDefinedMethods(protected: false, private: false, static: $static);
    }

    /**
     * @return array<ReflectionProperty>
     */
    public function getAllApiProperties(bool $static = true): array
    {
        return $this->getAllUserDefinedProperties(protected: false, private: false, static: $static);
    }
}
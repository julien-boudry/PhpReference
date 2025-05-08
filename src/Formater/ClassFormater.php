<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Formater;

use JulienBoudry\PhpReference\Reflect\ClassConstantWrapper;
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\MethodWrapper;
use JulienBoudry\PhpReference\Reflect\PropertyWrapper;

class ClassFormater
{
    /** @var array<string, ClassConstantWrapper> */
    public array $constEntries {
        get => $this->class->getAllConstants();
    }

    /** @var array<string, PropertyWrapper> */
    public array $staticPropertiesEntries {
        get => $this->class->getAllProperties(static: true, nonStatic: false);
    }

    /** @var array<string, MethodWrapper> */
    public array $staticMethodsEntries {
        get => $this->class->getAllApiMethods(static: true, nonStatic: false);
    }

    /** @var array<string, PropertyWrapper> */
    public array $PropertiesEntries {
        get => $this->class->getAllProperties(static: false);
    }

    /** @var array<string, MethodWrapper> */
    public array $MethodsEntries {
        get => $this->class->getAllUserDefinedMethods(static: false);
    }

    /** @var array<string, array<string, ?string>> */
    public array $index {
        get => array_merge(
            $this->indexApiConstants,
            $this->indexStaticProperties,
            $this->indexStaticMethods,
            $this->indexProperties,
            $this->indexMethods,
        );
    }

    /** @var array<string, array<string, ?string>> */
    public private(set) array $indexApiConstants = [];

    /** @var array<string, array<string, ?string>> */
    public private(set) array $indexStaticProperties = [];

    /** @var array<string, array<string, ?string>> */
    public private(set) array $indexStaticMethods = [];

    /** @var array<string, array<string, ?string>> */
    public private(set) array $indexProperties = [];

    /** @var array<string, array<string, ?string>> */
    public private(set) array $indexMethods = [];

    public function __construct(public readonly ClassWrapper $class)
    {
        $this->makeIndexApiConstants();
        $this->makeIndexStaticProperties();
        $this->makeIndexStaticMethods();
        $this->makeIndexProperties();
        $this->makeIndexMethods();
    }

    public function getDescription(): ?string
    {
        return $this->class->getDescription();
    }

    private function makeIndexApiConstants(): void
    {
        foreach ($this->constEntries as $name => $constant) {
            $this->indexApiConstants[$name]['name'] = $constant->reflection->getName();
            $this->indexApiConstants[$name]['type'] =  $constant->reflection->getType()?->__toString();
        }
    }

    private function makeIndexStaticProperties(): void
    {
        foreach ($this->staticPropertiesEntries as $name => $property) {
            $this->indexStaticProperties[$name] = [];

            $this->buildProperty($this->indexStaticProperties[$name], $property);
        }
    }

    private function makeIndexStaticMethods(): void
    {
        foreach ($this->staticMethodsEntries as $name => $method) {
            $this->indexStaticMethods[$name] = [];
            $this->buildMethod($this->indexStaticMethods[$name], $method);
        }
    }

    private function makeIndexProperties(): void
    {
        foreach ($this->PropertiesEntries as $name => $property) {
            $this->indexProperties[$name] = [];
            $this->buildProperty($this->indexProperties[$name], $property);
        }
    }

    private function makeIndexMethods(): void
    {
        foreach ($this->MethodsEntries as $name => $method) {
            $this->indexMethods[$name] = [];
            $this->buildMethod($this->indexMethods[$name], $method);
        }
    }

    private function buildProperty(array &$metadata, PropertyWrapper $property): void
    {
        $metadata['name'] = $property->reflection->getName();
        $metadata['type'] = $property->reflection->getType()?->__toString();
        $metadata['defaultValue'] = $property->reflection->getDefaultValue();
    }

    private function buildMethod(array &$metadata, MethodWrapper $method): void
    {
        $metadata['name'] = $method->reflection->getName();
        $metadata['returnType'] = $method->reflection->getReturnType()?->__toString();
    }
}
<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperties;
use ReflectionProperty;
use WeakReference;

abstract class ClassElementWrapper extends ReflectionWrapper
{
    protected static function formatDefaultValue(mixed $defaultValue): string
    {
        $defaultValue = var_export($defaultValue, true);
        $defaultValue = str_replace('NULL', 'null', $defaultValue);
        $defaultValue = str_replace("array (\n)", '[]', $defaultValue);

        return $defaultValue;
    }

    /** @var WeakReference<ClassWrapper> */
    public \WeakReference $classReference;

    public ?ClassWrapper $classWrapper {
        get => $this->classReference->get();
    }

    public bool $willBeInPublicApi {
        get => $this->hasApiTag && !$this->hasInternalTag && $this->classWrapper && $this->classWrapper->willBeInPublicApi;
    }

    public string $name {
        get => $this->reflection->getName();
    }

    public function __construct(
        ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflectorInClass,
        ClassWrapper $classWrapper
    )
    {
        $this->classReference = WeakReference::create($classWrapper);

        parent::__construct($reflectorInClass);
    }

    public function getPageDirectory(): string
    {
        return $this->classWrapper->getPageDirectory();
    }

    public function getModifierNames(): string
    {
        if (!method_exists($this->reflection, 'getModifiers')) {
            throw new LogicException('Method getModifiers() is not available on this reflection class.');
        }

        return implode(' ', Reflection::getModifierNames($this->reflection->getModifiers()));
    }

}
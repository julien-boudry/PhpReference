<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use HaydenPierce\ClassFinder\ClassFinder;
use JulienBoudry\PhpReference\Util;
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
    /** @var WeakReference<ClassWrapper> */
    public \WeakReference $classReference;

    public ?ClassWrapper $classWrapper {
        get => $this->classReference->get();
    }

    public bool $willBeInPublicApi {
        get => $this->hasApiTag && !$this->hasInternalTag && $this->classWrapper && $this->classWrapper->willBeInPublicApi;
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
}
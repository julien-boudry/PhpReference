<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\UrlLinker;
use JulienBoudry\PhpReference\Util;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

abstract class ReflectionWrapper
{
    /**
     * @param array<ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionFunction> $reflectors
     * @return array<ReflectionWrapper>
     */
    public static function toWrapper(array $reflectors, ClassWrapper $classWrapper): array
    {
        $wrappers = [];
        foreach ($reflectors as $reflector) {
            $wrappers[$reflector->getName()] = match (true) {
                $reflector instanceof ReflectionMethod => new MethodWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionProperty => new PropertyWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionClassConstant => new ClassConstantWrapper($reflector, $classWrapper),
                $reflector instanceof ReflectionFunction => new FunctionWrapper($reflector), // @phpstan-ignore instanceof.alwaysTrue
                default => throw new \LogicException('Unsupported reflector type: ' . get_class($reflector)),
            };
        }

        return $wrappers;
    }


    public readonly ?DocBlock $docBlock;

    public readonly bool $hasApiTag;
    public readonly bool $hasInternalTag;

    // @phpstan-ignore missingType.generics
    public ReflectionClass|ReflectionProperty|ReflectionFunctionAbstract|ReflectionClassConstant $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(protected readonly Reflector $reflector)
    {
         // Docblock
         $docComment = $this->reflection->getDocComment();
         $this->docBlock = !empty($docComment) ? Util::getDocBlocFactory()->create($docComment) : null;

        // DocBlock visibility
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
    }

    public ?string $name {
        get => $this->reflection->name ?? null;
    }

    public function getPageDirectory(): string
    {
        return "/ref";
    }

    public function getDescription(): ?string
    {
        return $this->docBlock?->getDescription()->render();
    }

    public function getShortDescriptionForTable(): ?string
    {
        $description = $this->getDescription();

        if ($description === null) {
            return null;
        }

        // Remove line breaks and replace with spaces
        $description = str_replace(["\r\n", "\r", "\n"], ' ', $description);

        // Remove characters that could break markdown tables
        $description = str_replace(['|', '`'], '', $description);

        // Remove extra spaces
        $description = mb_trim($description);

        // Truncate to x characters and add ellipsis if needed
        $maxLength = 200;

        if (strlen($description) > $maxLength) {
            $description = mb_substr($description, 0, $maxLength) . '...';
        }

        return $description;
    }

    public function getUrlLinker(): UrlLinker
    {
        if ($this instanceof WritableInterface) {
            return new UrlLinker($this);
        }

        throw new \LogicException('This wrapper does not implement WritableInterface');
    }
}
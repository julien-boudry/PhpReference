<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\UrlLinker;
use JulienBoudry\PhpReference\Util;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

abstract class ReflectionWrapper
{
    protected static function formatValue(mixed $defaultValue): string
    {
        if (is_array($defaultValue)) {
            return Util::arrayToString($defaultValue);
        }

        $defaultValue = var_export($defaultValue, true);
        $defaultValue = str_replace('NULL', 'null', $defaultValue);

        return $defaultValue;
    }

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
    public ReflectionClass|ReflectionProperty|ReflectionFunctionAbstract|ReflectionClassConstant|ReflectionParameter $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function __construct(protected readonly Reflector $reflector)
    {
         // Docblock
         $docComment = $reflector instanceof ReflectionParameter ? null : $this->reflection->getDocComment(); // @phpstan-ignore method.notFound
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

    public function getDocBlockTagDescription(string $tag, ?string $variableNameFilter = null) : ?string
    {
        if ($this->docBlock === null) {
            return null;
        }

        $tagObject = $this->docBlock->getTagsByName($tag);

        if (empty($tagObject)) {
            return null;
        }

        if ($variableNameFilter == null) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\BaseTag */
            $firstTag = $tagObject[0];
        } else {
            // Filter tags by variable name if provided
            $filteredTags = array_filter($tagObject, fn (Param $tag): bool => $tag->getVariableName() === $variableNameFilter);

            if (empty($filteredTags)) {
                return null;
            }

            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param */
            $firstTag = reset($filteredTags);
        }

        // Return the first tag description or value
        return $firstTag->getDescription()->render();
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

    public function getSourceLink(): ?string
    {
        if (    method_exists($this->reflection, 'getFileName') === false || method_exists($this->reflection, 'getStartLine') === false)
        {
            throw new LogicException("Method source link is not available on `{$this->name}` or the file does not exist.");
        }

        if ($this->reflection->getFileName() === false || $this->reflection->getStartLine() === false)
        {
            return null;
        }

        return 'https://github.com/julien-boudry/Condorcet/blob/master/' .
                substr($this->reflection->getFileName(), mb_strpos($this->reflection->getFileName(), '/src/') + 1) .
                '#L' . $this->reflection->getStartLine()
        ;
    }

    public function getUrlLinker(): UrlLinker
    {
        if ($this instanceof WritableInterface) {
            return new UrlLinker($this);
        }

        throw new \LogicException('This wrapper does not implement WritableInterface');
    }

    public function getModifierNames(): string
    {
        if (!method_exists($this->reflection, 'getModifiers')) {
            throw new LogicException('Method getModifiers() is not available on this reflection class.');
        }

        return implode(' ', Reflection::getModifierNames($this->reflection->getModifiers()));
    }
}
<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\{Execution, UrlLinker, Util};
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\{InvalidTag, Param};
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
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
        if (\is_array($defaultValue)) {
            return Util::arrayToString($defaultValue);
        }

        $defaultValue = var_export($defaultValue, true);

        return str_replace('NULL', 'null', $defaultValue);
    }

    /**
     * @param  array<ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionFunction>  $reflectors
     *
     * @return array<ReflectionWrapper>
     */
    public static function toWrapper(array $reflectors, ClassWrapper $classWrapper): array
    {
        $wrappers = [];
        foreach ($reflectors as $reflector) {
            if (method_exists($reflector, 'getDeclaringClass')) {
                $declaringClass = Execution::$instance->codeIndex->getClassWrapper($reflector->getDeclaringClass()->name);
            }

            $wrappers[$reflector->getName()] = match (true) {
                $reflector instanceof ReflectionMethod => new MethodWrapper($reflector, $classWrapper, $declaringClass),
                $reflector instanceof ReflectionProperty => new PropertyWrapper($reflector, $classWrapper, $declaringClass),
                $reflector instanceof ReflectionClassConstant => new ClassConstantWrapper($reflector, $classWrapper, $declaringClass),
                $reflector instanceof ReflectionFunction => new FunctionWrapper($reflector), // @phpstan-ignore instanceof.alwaysTrue
                default => throw new LogicException('Unsupported reflector type: ' . $reflector::class),
            };
        }

        return $wrappers;
    }

    public readonly ?DocBlock $docBlock;

    public readonly bool $hasApiTag;

    public readonly bool $hasInternalTag;

    public bool $willBeInPublicApi {
        get => Execution::$instance->publicApiDefinition->isPartOfPublicApi($this);
    }

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
        $this->docBlock = ! empty($docComment) ? Util::getDocBlocFactory()->create($docComment, Util::getDocBlocContextFactory()->createFromReflector($reflector)) : null;

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
        return '/ref';
    }

    public function getDescription(): ?string
    {
        return $this->docBlock?->getDescription()->render();
    }

    /**
     * Get all tags of a specific type from the DocBlock.
     *
     * @param  string  $tag  The tag name to filter by (e.g., 'param', 'return').
     * @param  string|null  $variableNameFilter  Optional variable name to filter by (for 'param' tags).
     *
     * @return ?array<DocBlock\Tags\BaseTag> Array of tags matching the criteria.
     */
    public function getDocBlockTags(string $tag, ?string $variableNameFilter = null): ?array
    {
        if ($this->docBlock === null) {
            return null;
        }

        /** @var Tag[] */
        $tagObjects = $this->docBlock->getTagsByName($tag);

        // Filtrer les objets par type si spécifié
        /** @var DocBlock\Tags\BaseTag[] */
        $tagObjects = array_filter($tagObjects, fn(Tag $tagObject): bool => !($tagObject instanceof InvalidTag));

        if (empty($tagObjects)) {
            return null;
        }

        if ($variableNameFilter === null) {
            return $tagObjects;
        } else {
            /** @var Param[] */
            return array_filter($tagObjects, fn(Param $tag): bool => $tag->getVariableName() === $variableNameFilter);
        }
    }

    /**
     * @return ?array<DocBlock\Tags\See>
     */
    public function getSeeTags(): ?array
    {
        /** @var ?DocBlock\Tags\See[] */
        return $this->getDocBlockTags('see');
    }

    /**
     *
     * @return ?array<array{destination: ClassElementWrapper|string, name: string, tag: DocBlock\Tags\See}>
     * @throws LogicException
     */
    public function getResolvedSeeTags(): ?array
    {
        $seeTags = $this->getSeeTags();

        if ($seeTags === null) {
            return null;
        }

        $resolved = [];

        // Resolve the see tags to their actual links
        foreach ($seeTags as $key => $seeTag) {
            // Resolve the reference to a URL
            $reference = $seeTag->getReference();
            $referenceRender = (string) $reference;

            if ($reference instanceof Url) {
                // If it's already a URL, just return it
                $resolved[$key] = [
                    'destination' => (string) $reference,
                    'name' => $referenceRender,
                    'tag' => $seeTag,
                ];
            }
            elseif ($reference instanceof Fqsen) {
                // If it's a class reference, resolve it to a ClassWrapper
                [$classPath, $elementName] = explode('::', $referenceRender);

                // Remove leading backslash if it's the first character
                if (str_starts_with($classPath, '\\')) {
                    $classPath = substr($classPath, 1);
                }

                $class = Execution::$instance->codeIndex->classList[$classPath] ?? null;

                if ($class === null) {
                    throw new LogicException("Class `{$classPath}` not found for see tag on {$this->name}");
                }

                $element = $class->getElementByName(str_replace('()', '', $elementName));

                if ($element === null) {
                    throw new LogicException("Element `{$elementName}` not found in class `{$classPath}` for see tag on {$this->name}");
                }

                $resolved[$key] = [
                    'destination' => $element,
                    'name' => $referenceRender,
                    'tag' => $seeTag,
                ];
            } else {
                throw new LogicException('Unsupported reference type in see tag: ' . get_class($reference));
            }
        }

        return $resolved;
    }

    public function getDocBlockTagDescription(string $tag, ?string $variableNameFilter = null): ?string
    {
        $tags = $this->getDocBlockTags($tag, $variableNameFilter);

        if (empty($tags)) {
            return null;
        }

        $firstTag = reset($tags);

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

        if (\strlen($description) > $maxLength) {
            $description = mb_substr($description, 0, $maxLength) . '...';
        }

        return $description;
    }

    public function getSourceLink(): ?string
    {
        if (method_exists($this->reflection, 'getFileName') === false || method_exists($this->reflection, 'getStartLine') === false) {
            throw new LogicException("Method source link is not available on `{$this->name}` or the file does not exist.");
        }

        if ($this->reflection->getFileName() === false || $this->reflection->getStartLine() === false) {
            return null;
        }

        return 'https://github.com/julien-boudry/Condorcet/blob/master/' .
                substr($this->reflection->getFileName(), mb_strpos($this->reflection->getFileName(), '/src/') + 1) .
                '#L' . $this->reflection->getStartLine();
    }

    public function getUrlLinker(): UrlLinker
    {
        if ($this instanceof WritableInterface) {
            return new UrlLinker($this);
        }

        throw new LogicException('This wrapper does not implement WritableInterface');
    }

    public function getModifierNames(): string
    {
        if (! method_exists($this->reflection, 'getModifiers')) {
            throw new LogicException('Method getModifiers() is not available on this reflection class.');
        }

        return implode(' ', Reflection::getModifierNames($this->reflection->getModifiers()));
    }
}

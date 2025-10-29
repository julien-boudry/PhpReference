<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\{Execution, UrlLinker, Util};
use JulienBoudry\PhpReference\Log\{InvalidManualTag, InvalidSeeTag};
use JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface;
use LogicException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\{InvalidTag, Param};
use phpDocumentor\Reflection\DocBlock\Tags\Reference\{Fqsen, Url};
use phpDocumentor\Reflection\Types\{Context, Object_};
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

use function Laravel\Prompts\warning;

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
     * @template T of ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionFunction
     *
     * @param  array<T>  $reflectors
     *
     * @return array<string, (T is ReflectionMethod ? MethodWrapper : (T is ReflectionProperty ? PropertyWrapper : (T is ReflectionClassConstant ? ClassConstantWrapper : FunctionWrapper)))>
     */
    public static function toWrapper(array $reflectors, ClassWrapper $classWrapper): array
    {
        $wrappers = [];
        foreach ($reflectors as $reflector) {
            $declaringClass = method_exists($reflector, 'getDeclaringClass') ? Execution::$instance->codeIndex->getClassWrapper($reflector->getDeclaringClass()->name) : null;

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

    public NamespaceWrapper $declaringNamespace;

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

    protected readonly UrlLinker $urlLinker;
    public readonly Context $docBlockContext;

    public function __construct(protected readonly Reflector $reflector)
    {
        // Docblock
        $docComment = $reflector instanceof ReflectionParameter ? null : $this->reflection->getDocComment(); // @phpstan-ignore method.notFound

        $this->docBlockContext ??= Util::getDocBlocContextFactory()->createFromReflector($reflector);
        $this->docBlock = ! empty($docComment) ? Util::getDocBlocFactory()->create($docComment, $this->docBlockContext) : null;

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
     * @return ?array<int, DocBlock\Tags\BaseTag> Array of tags matching the criteria.
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
     * @return ?array<int, DocBlock\Tags\See>
     */
    public function getSeeTags(): ?array
    {
        /** @var ?DocBlock\Tags\See[] */
        return $this->getDocBlockTags('see');
    }

    /**
     * @return ?array<int, DocBlock\Tags\BaseTag>
     */
    public function getManualTags(): ?array
    {
        $book = $this->getDocBlockTags('book') ?? [];
        $manual = $this->getDocBlockTags('manual') ?? [];

        /** @var DocBlock\Tags\BaseTag[] */
        $merged = array_merge($book, $manual);

        return empty($merged) ? null : $merged;
    }

    /**
     * @param ?array<int, DocBlock\Tags\See|DocBlock\Tags\Throws> $tags
     *
     * @throws LogicException
     *
     * @return ?array<int, array{destination: ReflectionWrapper|string, tag: DocBlock\Tags\See|DocBlock\Tags\BaseTag}>
     */
    protected function resolveTags(?array $tags): ?array
    {
        if ($tags === null) {
            return null;
        }

        $resolved = [];

        // Resolve the see tags to their actual links
        foreach ($tags as $key => $tag) {
            // Resolve the reference to a URL
            $reference = ($tag instanceof DocBlock\Tags\See) ? $tag->getReference() : $tag->getType();
            $referenceRender = (string) $reference;

            if ($reference instanceof Url) {
                // If it's already a URL, just return it
                $resolved[$key] = [
                    'destination' => (string) $reference,
                    'tag' => $tag,
                ];
            } elseif ($reference instanceof Object_) {
                // For Object_ references, we need the type information
                // See tags have getType(), Throws tags have getType()
                if (!method_exists($tag, 'getType')) {
                    continue;
                }

                $fqsenPath = (string) $tag->getType();
                if (str_starts_with($fqsenPath, '\\')) {
                    $fqsenPath = substr($fqsenPath, 1);
                }
                $destination = Execution::$instance->codeIndex->getClassWrapper($fqsenPath);

                // If it's already a URL, just return it
                $resolved[$key] = [
                    'destination' => $destination,
                    'tag' => $tag,
                ];
            } elseif ($reference instanceof Fqsen) {
                try {
                    $referenceString = (string) $reference;
                    // Transform only the last \ to :: before method name
                    if (str_ends_with($referenceString, '()')) {
                        $reformatedString = preg_replace('/(\\\\.+)\\\\([^:]+\(\))$/', '$1::$2', $referenceString);
                    } else {
                        $reformatedString = $referenceString;
                    }

                    $element = Execution::$instance->codeIndex->getElement($reformatedString);
                } catch (LogicException $e) {
                    // throw new InvalidSeeTag("Failed to resolve Fqsen reference in see tag on {$this->name}, message:: " . $e->getMessage());
                    warning("Failed to resolve Fqsen reference in see tag on {$this->name}, message:: " . $e->getMessage());

                    continue;
                }

                // If it's already a URL, just return it
                $resolved[$key] = [
                    'destination' => $element,
                    'tag' => $tag,
                ];
            } else {
                throw new LogicException('Unsupported reference type in see tag: ' . $reference::class);
            }
        }

        return $resolved;
    }

    /**
     * @throws LogicException
     *
     * @return ?array<int, array{destination: ClassElementWrapper|string, tag: DocBlock\Tags\See}>
     */
    public function getResolvedSeeTags(): ?array
    {
        $seeTags = $this->getSeeTags();

        $resolved = $this->resolveTags($seeTags);

        if ($resolved === null) {
            return null;
        }

        // Filter out any tags that match the ignored reflection
        return array_filter($resolved, fn(array $item): bool => $item['destination'] !== $this); // @phpstan-ignore return.type
    }

    /**
     * @return ?array<int, string>
     */
    public function getResolvedManualTags(): ?array
    {
        $ManualTags = $this->getManualTags();

        if ($ManualTags === null) {
            return null;
        }

        $resolved = [];

        try {
            foreach ($ManualTags as $key => $tag) {
                $resolved[$key] = \constant($tag->getDescription()->render())->value;
            }
        } catch (\Error $e) {
            throw new InvalidManualTag("Invalid manual tag on {$this->name} / " . $e->getMessage());
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

        return 'https://github.com/julien-boudry/Condorcet/blob/master/'
                . substr($this->reflection->getFileName(), mb_strpos($this->reflection->getFileName(), '/src/') + 1)
                . '#L' . $this->reflection->getStartLine();
    }

    public function getUrlLinker(): UrlLinker
    {
        if ($this instanceof WritableInterface) {
            $this->urlLinker ??= new UrlLinker($this);

            return $this->urlLinker;
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

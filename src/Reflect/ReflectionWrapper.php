<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\{Execution, UrlLinker, Util};
use JulienBoudry\PhpReference\Exception\UnsupportedOperationException;
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

/**
 * Abstract base class for all PHP reflection wrappers.
 *
 * This class provides the foundation for enhanced reflection capabilities,
 * including PHPDoc parsing via phpDocumentor. All wrapper classes extend
 * this base to gain common functionality.
 *
 * Key features:
 * - PHPDoc parsing and caching
 * - Detection of @api and @internal tags
 * - Summary and description extraction
 * - @see and @throws tag resolution
 * - Source link generation
 * - Modifier name generation
 *
 * @see ClassWrapper For class/interface/trait/enum wrappers
 * @see ClassElementWrapper For class member wrappers
 * @see FunctionWrapper For standalone function wrappers
 * @see ParameterWrapper For function/method parameter wrappers
 */
abstract class ReflectionWrapper
{
    /**
     * Formats a PHP value for display in documentation.
     *
     * Handles arrays (converting to readable string representation),
     * and converts NULL to lowercase 'null'.
     *
     * @param mixed $defaultValue The value to format
     */
    protected static function formatValue(mixed $defaultValue): string
    {
        if (\is_array($defaultValue)) {
            return Util::arrayToString($defaultValue);
        }

        $defaultValue = var_export($defaultValue, true);

        return str_replace('NULL', 'null', $defaultValue);
    }

    /**
     * Converts an array of PHP reflectors to their corresponding wrapper classes.
     *
     * This factory method creates the appropriate wrapper type based on the
     * reflector type (method, property, constant, or function).
     *
     * @template T of ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionFunction
     *
     * @param array<T>     $reflectors   Array of PHP reflectors to wrap
     * @param ClassWrapper $classWrapper The parent class wrapper for context
     *
     * @throws LogicException If an unsupported reflector type is provided
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

    /**
     * The namespace wrapper this element belongs to.
     */
    public NamespaceWrapper $declaringNamespace;

    /**
     * The parsed PHPDoc block, or null if no documentation exists.
     */
    public readonly ?DocBlock $docBlock;

    /**
     * Whether this element has an @api tag in its PHPDoc.
     */
    public readonly bool $hasApiTag;

    /**
     * Whether this element has an @internal tag in its PHPDoc.
     */
    public readonly bool $hasInternalTag;

    /**
     * Whether this element is part of the public API (computed dynamically).
     */
    public bool $willBeInPublicApi {
        get => Execution::$instance->publicApiDefinition->isPartOfPublicApi($this);
    }

    /**
     * The underlying PHP reflector.
     */
    // @phpstan-ignore missingType.generics
    public ReflectionClass|ReflectionProperty|ReflectionFunctionAbstract|ReflectionClassConstant|ReflectionParameter $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    /**
     * Cached URL linker for this element.
     */
    protected readonly UrlLinker $urlLinker;

    /**
     * The context for resolving type aliases in PHPDoc.
     */
    public readonly Context $docBlockContext;

    /**
     * Creates a new reflection wrapper.
     *
     * Parses the PHPDoc comment (if present) and extracts @api and @internal tags.
     *
     * @param Reflector $reflector The PHP reflector to wrap
     */
    public function __construct(protected readonly Reflector $reflector)
    {
        // Docblock
        $docComment = $reflector instanceof ReflectionParameter ? null : $this->reflection->getDocComment(); // @phpstan-ignore method.notFound

        // For ReflectionFunction, we need to create the context manually
        if ($reflector instanceof ReflectionFunction) {
            $namespaceName = $reflector->getNamespaceName();
            $this->docBlockContext ??= new Context($namespaceName ?: '');
        } else {
            $this->docBlockContext ??= Util::getDocBlocContextFactory()->createFromReflector($reflector);
        }

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

    /**
     * The name of this element.
     */
    public ?string $name {
        get => $this->reflection->name ?? null;
    }

    /**
     * Returns the base directory for this element's documentation page.
     *
     * Override in subclasses to provide element-specific directories.
     */
    public function getPageDirectory(): string
    {
        return '/ref';
    }

    /**
     * Get the summary from the DocBlock (first line/paragraph).
     *
     * phpDocumentor separates the DocBlock into:
     * - Summary: The first line/paragraph (before the first blank line)
     * - Description: Everything after the first blank line
     *
     * This method returns only the summary part.
     */
    public function getSummary(): ?string
    {
        if ($this->docBlock === null) {
            return null;
        }

        $summary = $this->docBlock->getSummary();

        return !empty($summary) ? $summary : null;
    }

    /**
     * Get the full description from the DocBlock, combining summary and description.
     *
     * phpDocumentor separates the DocBlock into:
     * - Summary: The first line/paragraph (before the first blank line)
     * - Description: Everything after the first blank line
     *
     * This method combines both to return the complete documentation text.
     */
    public function getDescription(): ?string
    {
        if ($this->docBlock === null) {
            return null;
        }

        $summary = $this->getSummary();
        $description = $this->docBlock->getDescription()->render();

        // Combine summary and description
        if ($summary !== null && !empty($description)) {
            return $summary . "\n\n" . $description;
        }

        if ($summary !== null) {
            return $summary;
        }

        if (!empty($description)) {
            return $description;
        }

        return null;
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
     * Returns all @see tags from the DocBlock.
     *
     * @return DocBlock\Tags\See[]|null
     */
    public function getSeeTags(): ?array
    {
        /** @var ?DocBlock\Tags\See[] */
        return $this->getDocBlockTags('see');
    }

    /**
     * Returns all @book and @manual tags from the DocBlock.
     *
     * These custom tags are used for linking to external documentation.
     *
     * @return DocBlock\Tags\BaseTag[]|null
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
     * Resolves @see or @throws tags to their referenced elements.
     *
     * Converts tag references to either ReflectionWrapper instances
     * (for documented elements) or strings (for URLs or external elements).
     *
     * @param DocBlock\Tags\See[]|DocBlock\Tags\Throws[]|null $tags The tags to resolve
     *
     * @throws LogicException If an unsupported reference type is encountered
     *
     * @return array<int, array{destination: ReflectionWrapper|string, tag: DocBlock\Tags\See|DocBlock\Tags\BaseTag}>|null
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

                    // Handle short syntax like @see methodName() without class name
                    // phpDocumentor resolves it to \Namespace\methodName() but we need \Namespace\ClassName::methodName()
                    if (!str_contains($referenceString, '::')) {
                        // Check if this is a method/property reference (ends with () or starts with $)
                        $lastBackslash = strrpos($referenceString, '\\');
                        if ($lastBackslash !== false) {
                            $elementPart = substr($referenceString, $lastBackslash + 1);
                            // If it looks like a method or property, try to resolve it relative to the declaring class
                            if (str_ends_with($elementPart, '()') || str_starts_with($elementPart, '$')) {
                                // Get the declaring class name if available
                                $declaringClassName = null;
                                if ($this instanceof ClassElementWrapper) {
                                    $declaringClassName = $this->inDocParentWrapper->name;
                                } elseif ($this instanceof ClassWrapper) {
                                    $declaringClassName = $this->name;
                                }

                                if ($declaringClassName !== null) {
                                    $referenceString = '\\' . $declaringClassName . '::' . $elementPart;
                                }
                            }
                        }
                    }

                    // Transform only the last \ to :: before method name
                    if (str_ends_with($referenceString, '()') && !str_contains($referenceString, '::')) {
                        $reformatedString = preg_replace('/(\\\\.+)\\\\([^:]+\(\))$/', '$1::$2', $referenceString);
                    } else {
                        $reformatedString = $referenceString;
                    }

                    $element = Execution::$instance->codeIndex->getClassElement($reformatedString);
                } catch (LogicException $e) {
                    // Collect the error instead of throwing or displaying a warning
                    Execution::$instance->errorCollector->addWarning(
                        message: "Failed to resolve @see reference: {$referenceString}",
                        context: "Element: {$this->name}",
                        element: $this,
                    );

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
     * Returns resolved @see tags with linked destination elements.
     *
     * Filters out self-references and returns an array where each item
     * contains the destination (ReflectionWrapper or URL string) and the
     * original tag.
     *
     * @throws LogicException If tag resolution fails
     *
     * @return array<int, array{destination: ClassElementWrapper|string, tag: DocBlock\Tags\See}>|null
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
     * Returns resolved @manual and @book tags as URL strings.
     *
     * These tags reference constants that contain URL values for external
     * documentation links.
     *
     * @return array<int, string>|null
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
            // Log the error instead of throwing - invalid tag is a data quality issue
            Execution::$instance->errorCollector->addWarning(
                message: "Invalid @manual or @book tag: {$e->getMessage()}",
                context: "Element: {$this->name}",
                element: $this,
            );

            return null;
        }

        return $resolved;
    }

    /**
     * Returns the description text from a specific PHPDoc tag.
     *
     * Returns the rendered description from the first matching tag.
     *
     * @param string      $tag                 The tag name (e.g., 'return', 'param')
     * @param string|null $variableNameFilter  For @param tags, filter by variable name
     *
     * @return string|null The tag description, or null if not found
     */
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

    /**
     * Get a short description suitable for display in tables.
     * Uses only the summary (first line/paragraph) to keep it concise.
     */
    public function getShortDescriptionForTable(): ?string
    {
        $description = $this->getSummary();

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

    /**
     * Returns the URL link to the source code of this element.
     *
     * Generates a link to view the source code in a repository browser
     * (e.g., GitHub). Requires source-url-base to be configured.
     *
     * @throws LogicException If the reflection doesn't support file/line info
     *
     * @return string|null The source URL, or null if not configured/available
     */
    public function getSourceLink(): ?string
    {
        if (method_exists($this->reflection, 'getFileName') === false || method_exists($this->reflection, 'getStartLine') === false) {
            throw new LogicException("Method source link is not available on `{$this->name}` or the file does not exist.");
        }

        if ($this->reflection->getFileName() === false || $this->reflection->getStartLine() === false) {
            return null;
        }

        $sourceUrlBase = Execution::$instance->config->getSourceUrlBase();

        if ($sourceUrlBase === null) {
            return null;
        }

        return $sourceUrlBase . '/'
                . substr($this->reflection->getFileName(), mb_strpos($this->reflection->getFileName(), '/src/') + 1)
                . '#L' . $this->reflection->getStartLine();
    }

    /**
     * Returns a URL linker configured for this element.
     *
     * Only available for elements that implement WritableInterface.
     *
     * @throws UnsupportedOperationException If this element doesn't support URL linking
     */
    public function getUrlLinker(): UrlLinker
    {
        if ($this instanceof WritableInterface) {
            $this->urlLinker ??= new UrlLinker($this);

            return $this->urlLinker;
        }

        throw new UnsupportedOperationException(
            operation: 'getUrlLinker',
            wrapperType: static::class,
        );
    }

    /**
     * Returns the visibility and other modifier names as a string.
     *
     * Returns modifiers like "public static", "protected final", etc.
     *
     * @throws UnsupportedOperationException If the underlying reflector doesn't support modifiers
     */
    public function getModifierNames(): string
    {
        if (! method_exists($this->reflection, 'getModifiers')) {
            throw new UnsupportedOperationException(
                operation: 'getModifierNames',
                wrapperType: static::class,
            );
        }

        return implode(' ', Reflection::getModifierNames($this->reflection->getModifiers()));
    }
}

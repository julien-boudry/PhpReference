<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use phpDocumentor\Reflection\{DocBlockFactory, DocBlockFactoryInterface};
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionType;

/**
 * Utility class providing helper functions for documentation generation.
 *
 * This class contains static methods for:
 * - DocBlock parsing factory management
 * - Array to string conversion for displaying default values
 * - Type to Markdown conversion with automatic cross-linking
 *
 * The DocBlock factories are lazily instantiated and cached as static properties
 * for performance, since they are used extensively throughout the application.
 */
class Util
{
    /**
     * Cached DocBlockFactory instance for parsing PHPDoc comments.
     */
    protected static DocBlockFactoryInterface $docBlockFactory;

    /**
     * Cached ContextFactory instance for resolving type aliases in DocBlocks.
     */
    protected static ContextFactory $docBlockContextFactory;

    /**
     * Returns the DocBlockFactory instance for parsing PHPDoc comments.
     *
     * The factory is lazily created on first access and cached for reuse.
     */
    public static function getDocBlocFactory(): DocBlockFactoryInterface
    {
        self::$docBlockFactory ??= DocBlockFactory::createInstance();

        return self::$docBlockFactory;
    }

    /**
     * Returns the ContextFactory for resolving type aliases in DocBlocks.
     *
     * The context factory is used to resolve class aliases and imports
     * when parsing type information from DocBlocks.
     */
    public static function getDocBlocContextFactory(): ContextFactory
    {
        self::$docBlockContextFactory ??= new ContextFactory;

        return self::$docBlockContextFactory;
    }

    /**
     * Converts a PHP array to a string representation suitable for documentation.
     *
     * This method produces a clean, readable string representation of arrays,
     * handling nested arrays, associative arrays, and various value types.
     * It intelligently omits keys for sequential 0-indexed arrays.
     *
     * @param array<int|string, mixed> $array The array to convert
     * @param $depth Current nesting depth (used internally for recursion)
     *
     * @return string The string representation (e.g., "['a', 'b']" or "['key' => 'value']")
     */
    public static function arrayToString(array $array, int $depth = 0): string
    {
        if (empty($array)) {
            return '[]';
        }

        $result = '[';

        // Vérifier si c'est un array indexé qui commence à 0
        $isIndexed = array_keys($array) === range(0, \count($array) - 1);

        // last key
        $lastKey = array_key_last($array);

        foreach ($array as $key => $value) {
            // N'afficher la clé que si ce n'est pas un array indexé commençant à 0
            if (! $isIndexed) {
                if (\is_string($key)) {
                    $result .= "'{$key}' => ";
                } else {
                    $result .= "{$key} => ";
                }
            }

            if (\is_array($value)) {
                $result .= self::arrayToString($value, $depth + 1);
            } elseif (\is_string($value)) {
                $result .= "'{$value}'";
            } elseif ($value === null) {
                $result .= 'null';
            } elseif (\is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } else {
                $result .= $value;
            }

            $result .= $key === $lastKey ? '' : ', ';
        }

        $result .= ']';

        return $result;
    }

    /**
     * Converts a ReflectionType to Markdown with automatic cross-linking.
     *
     * This method converts type information to Markdown format, automatically
     * creating links to documented classes in the code index. It handles:
     * - Simple named types
     * - Nullable types
     * - Union types (A|B)
     * - Intersection types (A&B)
     *
     * @param ReflectionType|null $type      The type to convert
     * @param UrlLinker           $urlLinker The linker for generating relative URLs
     *
     * @return string|null Markdown representation of the type, or null if no type
     */
    public static function getTypeMd(?ReflectionType $type, UrlLinker $urlLinker): ?string
    {
        if ($type === null) {
            return null;
        }

        return self::processReflectionType($type, $urlLinker);
    }

    /**
     * Recursively processes a ReflectionType to generate Markdown.
     *
     * This internal method handles the recursive nature of union and intersection
     * types, converting each component type and joining them with the appropriate
     * operator.
     *
     * @param ReflectionType $type      The type to process
     * @param UrlLinker      $urlLinker The linker for generating relative URLs
     *
     * @return string Markdown representation of the type
     */
    private static function processReflectionType(ReflectionType $type, UrlLinker $urlLinker): string
    {
        // Handle Union types (|)
        if ($type instanceof \ReflectionUnionType) {
            $types = array_map(
                fn(ReflectionType $t) => self::processReflectionType($t, $urlLinker),
                $type->getTypes()
            );

            return implode(' | ', $types);
        }

        // Handle Intersection types (&)
        if ($type instanceof \ReflectionIntersectionType) {
            $types = array_map(
                fn(ReflectionType $t) => self::processReflectionType($t, $urlLinker),
                $type->getTypes()
            );

            return implode(' & ', $types);
        }

        // Handle Named types (including built-in types and classes)
        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            $nullable = $type->allowsNull() && $typeName !== 'mixed';
            $typeString = ($nullable ? '?' : '') . $typeName;

            if (\array_key_exists($typeName, Execution::$instance->codeIndex->elementsList)) {
                $pageDestination = Execution::$instance->codeIndex->elementsList[$typeName];
                $toLink = $urlLinker->to($pageDestination);

                return "[`{$typeString}`]({$toLink})";
            }

            return "`{$typeString}`";
        }

        // Fallback for any other ReflectionType implementations
        $typeString = (string) $type;

        return "`{$typeString}`";
    }
}

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use phpDocumentor\Reflection\{DocBlockFactory, DocBlockFactoryInterface};
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionType;

class Util
{
    protected static DocBlockFactoryInterface $docBlockFactory;
    protected static ContextFactory $docBlockContextFactory;

    public static function getDocBlocFactory(): DocBlockFactoryInterface
    {
        self::$docBlockFactory ??= DocBlockFactory::createInstance();

        return self::$docBlockFactory;
    }

    public static function getDocBlocContextFactory(): ContextFactory
    {
        self::$docBlockContextFactory ??= new ContextFactory;

        return self::$docBlockContextFactory;
    }

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

    public static function getTypeMd(?ReflectionType $type, UrlLinker $urlLinker): ?string
    {
        if ($type === null) {
            return null;
        }

        return self::processReflectionType($type, $urlLinker);
    }

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

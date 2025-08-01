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

        $type = (string) $type;

        // Parse type and determine separator
        $separator = null;
        $types = [];

        if (str_contains($type, '|')) {
            $separator = ' | ';
            $types = array_map(trim(...), explode('|', $type));
        } elseif (str_contains($type, '&')) {
            $separator = ' & ';
            $types = array_map(trim(...), explode('&', $type));
        } else {
            // Named type (single type)
            $types = [$type];
        }

        return implode(
            $separator ?? '',
            array_map(
                function (string $type) use ($urlLinker): string {
                    $pureType = str_replace('?', '', $type); // Remove nullable type indicator

                    if (\array_key_exists($pureType, Execution::$instance->codeIndex->classList)) {
                        $pageDestination = Execution::$instance->codeIndex->classList[$pureType];

                        $toLink = $urlLinker->to($pageDestination);

                        return "[`{$type}`]({$toLink})";
                    }

                    return "`{$type}`";
                },
                $types
            )
        );
    }
}

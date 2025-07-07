<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;

class Util
{
    protected static DocBlockFactoryInterface $docBlockFactory;

    public static function getDocBlocFactory(): DocBlockFactoryInterface
    {
        self::$docBlockFactory ??= DocBlockFactory::createInstance();

        return self::$docBlockFactory;
    }

    public static function arrayToString(array $array, int $depth = 0): string
    {
        if (empty($array)) {
            return '[]';
        }

        $result = '[';

        // Vérifier si c'est un array indexé qui commence à 0
        $isIndexed = array_keys($array) === range(0, count($array) - 1);

        // last key
        $lastKey = array_key_last($array);

        foreach ($array as $key => $value) {
            // N'afficher la clé que si ce n'est pas un array indexé commençant à 0
            if (! $isIndexed) {
                if (is_string($key)) {
                    $result .= "'{$key}' => ";
                } else {
                    $result .= "{$key} => ";
                }
            }

            if (is_array($value)) {
                $result .= self::arrayToString($value, $depth + 1);
            } elseif (is_string($value)) {
                $result .= "'{$value}'";
            } elseif (is_null($value)) {
                $result .= 'null';
            } elseif (is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } else {
                $result .= $value;
            }

            $result .= $key === $lastKey ? '' : ', ';
        }

        $result .= ']';

        return $result;
    }
}

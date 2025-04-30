<?php declare(strict_types=1);

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
}
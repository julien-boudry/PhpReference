<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use JulienBoudry\PhpReference\Reflect\Capabilities\SignatureInterface;
use ReflectionClassConstant;

class ClassConstantWrapper extends ClassElementWrapper implements SignatureInterface
{
    public ReflectionClassConstant $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function getSignature(bool $withClassName = false): string
    {
        $type = $this->reflection->getType() ? ' '.((string) $this->reflection->getType()).' ' : ' ';
        $value = self::formatValue($this->reflection->getValue());

        $name = $this->name;

        if ($withClassName) {
            $name = $this->inDocParentWrapper->shortName.'::'.$name;
        }

        return "{$this->getModifierNames()} const{$type}{$name} = {$value}";
    }
}

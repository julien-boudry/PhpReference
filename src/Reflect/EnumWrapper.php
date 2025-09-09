<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Reflect;

use Roave\BetterReflection\Reflection\ReflectionEnum;
use Roave\BetterReflection\Reflection\ReflectionEnumBackedCase;

class EnumWrapper extends ClassWrapper
{
    public const string TYPE = 'enum';

    public ReflectionEnum $reflection {
        get {
            return $this->reflector; // @phpstan-ignore return.type
        }
    }

    public function isBacked(): bool
    {
        return $this->reflection->isBacked();
    }

    public function getBackedType(): string
    {
        if (! $this->isBacked()) {
            throw new \LogicException('This enum is not backed, so it has no backing type.');
        }

        $type = $this->reflection->getBackingType();

        return (string) $type;
    }

    protected function getHeritageHeadSignature(): string
    {
        $backed = $this->isBacked() ? ': ' . $this->getBackedType() : '';

        return $backed . parent::getHeritageHeadSignature();
    }

    protected function getInsideClassSignature(bool $onlyApi): string
    {
        $signature = parent::getInsideClassSignature($onlyApi);

        $casesSignatures = '';

        foreach ($this->reflection->getCases() as $case) {
            $casesSignatures .= '    case ' . $case->getName();

            if ($case instanceof ReflectionEnumBackedCase) {
                $backingValue = $case->getBackingValue();
                $backingValue = \is_string($backingValue) ? '"' . $backingValue . '"' : (string) $backingValue;

                $casesSignatures .= ' = ' . $backingValue;
            }

            $casesSignatures .= ";\n";
        }

        return $casesSignatures . $signature;
    }
}

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, EnumWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{SimpleEnumFixture, StringBackedEnumFixture, IntBackedEnumFixture};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('EnumWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Simple Enum (unit enum)', function (): void {
        beforeEach(function (): void {
            $this->enumWrapper = new EnumWrapper(new ReflectionEnum(SimpleEnumFixture::class));
        });

        it('wraps a simple enum correctly', function (): void {
            expect($this->enumWrapper->name)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures\\SimpleEnumFixture')
                ->and($this->enumWrapper->shortName)->toBe('SimpleEnumFixture');
        });

        it('identifies as an enum', function (): void {
            expect($this->enumWrapper->reflection->isEnum())->toBeTrue();
        });

        it('is not a backed enum', function (): void {
            expect($this->enumWrapper->isBacked())->toBeFalse();
        });

        it('throws exception when getting backing type on non-backed enum', function (): void {
            expect(fn() => $this->enumWrapper->getBackedType())
                ->toThrow(LogicException::class, 'This enum is not backed');
        });

        it('has enum cases', function (): void {
            $cases = $this->enumWrapper->reflection->getCases();

            expect($cases)->toHaveCount(3)
                ->and(array_map(fn($c) => $c->getName(), $cases))->toBe(['First', 'Second', 'Third']);
        });

        it('generates correct page path with enum type', function (): void {
            $path = $this->enumWrapper->getPagePath();

            expect($path)->toContain('SimpleEnumFixture')
                ->and($path)->toContain('enum_SimpleEnumFixture.md');
        });

        it('generates signature with cases', function (): void {
            $signature = $this->enumWrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('enum')
                ->and($signature)->toContain('SimpleEnumFixture')
                ->and($signature)->toContain('case First')
                ->and($signature)->toContain('case Second')
                ->and($signature)->toContain('case Third');
        });

        it('has correct TYPE constant', function (): void {
            expect(EnumWrapper::TYPE)->toBe('enum');
        });
    });

    describe('String-backed Enum', function (): void {
        beforeEach(function (): void {
            $this->enumWrapper = new EnumWrapper(new ReflectionEnum(StringBackedEnumFixture::class));
        });

        it('is a backed enum', function (): void {
            expect($this->enumWrapper->isBacked())->toBeTrue();
        });

        it('has string backing type', function (): void {
            expect($this->enumWrapper->getBackedType())->toBe('string');
        });

        it('has cases with string values', function (): void {
            $cases = $this->enumWrapper->reflection->getCases();

            expect($cases)->toHaveCount(3);

            $caseValues = [];
            foreach ($cases as $case) {
                if ($case instanceof ReflectionEnumBackedCase) {
                    $caseValues[$case->getName()] = $case->getBackingValue();
                }
            }

            expect($caseValues)->toBe([
                'Active' => 'active',
                'Pending' => 'pending',
                'Inactive' => 'inactive',
            ]);
        });

        it('generates signature with backing type and values', function (): void {
            $signature = $this->enumWrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('enum')
                ->and($signature)->toContain('StringBackedEnumFixture')
                ->and($signature)->toContain(': string')
                ->and($signature)->toContain('case Active = "active"')
                ->and($signature)->toContain('case Pending = "pending"')
                ->and($signature)->toContain('case Inactive = "inactive"');
        });

        it('has methods accessible through wrapper', function (): void {
            $methods = $this->enumWrapper->methods;

            expect($methods)->toHaveKey('getLabel')
                ->and($methods)->toHaveKey('isActive')
                ->and($methods)->toHaveKey('values');
        });

        it('detects static methods', function (): void {
            $valuesMethod = $this->enumWrapper->methods['values'];

            expect($valuesMethod->reflection->isStatic())->toBeTrue();
        });

        it('detects non-static methods', function (): void {
            $getLabelMethod = $this->enumWrapper->methods['getLabel'];

            expect($getLabelMethod->reflection->isStatic())->toBeFalse();
        });
    });

    describe('Int-backed Enum', function (): void {
        beforeEach(function (): void {
            $this->enumWrapper = new EnumWrapper(new ReflectionEnum(IntBackedEnumFixture::class));
        });

        it('is a backed enum', function (): void {
            expect($this->enumWrapper->isBacked())->toBeTrue();
        });

        it('has int backing type', function (): void {
            expect($this->enumWrapper->getBackedType())->toBe('int');
        });

        it('has cases with integer values', function (): void {
            $cases = $this->enumWrapper->reflection->getCases();

            $caseValues = [];
            foreach ($cases as $case) {
                if ($case instanceof ReflectionEnumBackedCase) {
                    $caseValues[$case->getName()] = $case->getBackingValue();
                }
            }

            expect($caseValues)->toBe([
                'Low' => 1,
                'Medium' => 5,
                'High' => 10,
                'Critical' => 100,
            ]);
        });

        it('generates signature with int backing type', function (): void {
            $signature = $this->enumWrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('enum')
                ->and($signature)->toContain('IntBackedEnumFixture')
                ->and($signature)->toContain(': int')
                ->and($signature)->toContain('case Low = 1')
                ->and($signature)->toContain('case Critical = 100');
        });
    });

    describe('Enum with ClassWrapper compatibility', function (): void {
        it('ClassWrapper detects enum', function (): void {
            $classWrapper = new ClassWrapper(new ReflectionClass(StringBackedEnumFixture::class));

            expect($classWrapper->reflection->isEnum())->toBeTrue();
        });

        it('can get constants from enum via ClassWrapper', function (): void {
            // Enums can have constants alongside cases
            $classWrapper = new ClassWrapper(new ReflectionClass(StringBackedEnumFixture::class));
            $constants = $classWrapper->constants;

            // Enum cases are not class constants, they are enum cases
            expect($constants)->toBeArray();
        });
    });
});

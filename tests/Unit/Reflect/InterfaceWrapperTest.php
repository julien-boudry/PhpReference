<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{InterfaceWrapper, ClassWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{InterfaceFixture, SecondaryInterfaceFixture};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('InterfaceWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Basic Interface', function (): void {
        beforeEach(function (): void {
            $this->interfaceWrapper = new InterfaceWrapper(new ReflectionClass(InterfaceFixture::class));
        });

        it('wraps an interface correctly', function (): void {
            expect($this->interfaceWrapper->name)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures\\InterfaceFixture')
                ->and($this->interfaceWrapper->shortName)->toBe('InterfaceFixture');
        });

        it('identifies as an interface', function (): void {
            expect($this->interfaceWrapper->reflection->isInterface())->toBeTrue()
                ->and($this->interfaceWrapper->reflection->isAbstract())->toBeTrue();
        });

        it('has correct TYPE constant', function (): void {
            expect(InterfaceWrapper::TYPE)->toBe('interface');
        });

        it('generates correct page path with interface type', function (): void {
            $path = $this->interfaceWrapper->getPagePath();

            expect($path)->toContain('InterfaceFixture')
                ->and($path)->toContain('interface_InterfaceFixture.md');
        });

        it('has interface methods', function (): void {
            $methods = $this->interfaceWrapper->methods;

            expect($methods)->toHaveKey('getName')
                ->and($methods)->toHaveKey('setValue')
                ->and($methods)->toHaveKey('hasKey')
                ->and($methods)->toHaveKey('create');
        });

        it('all methods are public and abstract', function (): void {
            $methods = $this->interfaceWrapper->methods;

            foreach ($methods as $method) {
                expect($method->reflection->isPublic())->toBeTrue()
                    ->and($method->reflection->isAbstract())->toBeTrue();
            }
        });

        it('has interface constants', function (): void {
            $constants = $this->interfaceWrapper->constants;

            expect($constants)->toHaveKey('INTERFACE_CONST')
                ->and($constants)->toHaveKey('VERSION');
        });

        it('gets constant values correctly', function (): void {
            $interfaceConst = $this->interfaceWrapper->constants['INTERFACE_CONST'];
            $versionConst = $this->interfaceWrapper->constants['VERSION'];

            expect($interfaceConst->reflection->getValue())->toBe('interface_value')
                ->and($versionConst->reflection->getValue())->toBe(1);
        });

        it('detects static interface method', function (): void {
            $createMethod = $this->interfaceWrapper->methods['create'];

            expect($createMethod->reflection->isStatic())->toBeTrue();
        });

        it('generates signature correctly', function (): void {
            $signature = $this->interfaceWrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('interface')
                ->and($signature)->toContain('InterfaceFixture')
                ->and($signature)->not->toContain('final') // 'final' should be stripped for interfaces
                ->and($signature)->toContain('INTERFACE_CONST')
                ->and($signature)->toContain('getName')
                ->and($signature)->toContain('setValue');
        });

        it('can get method parameters from interface methods', function (): void {
            $setValueMethod = $this->interfaceWrapper->methods['setValue'];
            $params = $setValueMethod->getParameters();

            expect($params)->toHaveCount(2);

            $keyParam = $params[0];
            $valueParam = $params[1];

            expect($keyParam->name)->toBe('key')
                ->and($valueParam->name)->toBe('value');
        });
    });

    describe('Secondary Interface', function (): void {
        beforeEach(function (): void {
            $this->interfaceWrapper = new InterfaceWrapper(new ReflectionClass(SecondaryInterfaceFixture::class));
        });

        it('wraps the secondary interface', function (): void {
            expect($this->interfaceWrapper->shortName)->toBe('SecondaryInterfaceFixture');
        });

        it('has the process method', function (): void {
            $methods = $this->interfaceWrapper->methods;

            expect($methods)->toHaveKey('process');
        });

        it('has secondary constant', function (): void {
            $constants = $this->interfaceWrapper->constants;

            expect($constants)->toHaveKey('SECONDARY_CONST')
                ->and($constants['SECONDARY_CONST']->reflection->getValue())->toBe('secondary');
        });
    });

    describe('ClassWrapper compatibility with interfaces', function (): void {
        it('ClassWrapper can wrap an interface', function (): void {
            $classWrapper = new ClassWrapper(new ReflectionClass(InterfaceFixture::class));

            expect($classWrapper->reflection->isInterface())->toBeTrue()
                ->and($classWrapper->shortName)->toBe('InterfaceFixture');
        });

        it('ClassWrapper gets interface methods', function (): void {
            $classWrapper = new ClassWrapper(new ReflectionClass(InterfaceFixture::class));
            $methods = $classWrapper->methods;

            expect($methods)->toHaveKey('getName');
        });

        it('InterfaceWrapper extends ClassWrapper', function (): void {
            $interfaceWrapper = new InterfaceWrapper(new ReflectionClass(InterfaceFixture::class));

            expect($interfaceWrapper)->toBeInstanceOf(ClassWrapper::class);
        });
    });
});

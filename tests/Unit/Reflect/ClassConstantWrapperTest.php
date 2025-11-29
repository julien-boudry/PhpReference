<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, ClassConstantWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{ClassFixture, InterfaceFixture, BaseClassFixture, ChildClassFixture};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassConstantWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Class Constants', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
        });

        it('wraps constants correctly', function (): void {
            $constants = $this->classWrapper->constants;

            expect($constants)->not->toBeEmpty()
                ->and($constants)->toHaveKey('PUBLIC_CONST')
                ->and($constants)->toHaveKey('PROTECTED_CONST')
                ->and($constants)->toHaveKey('PRIVATE_CONST');
        });

        it('gets public constant value', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];

            expect($constant)->toBeInstanceOf(ClassConstantWrapper::class)
                ->and($constant->name)->toBe('PUBLIC_CONST')
                ->and($constant->reflection->getValue())->toBe('public_value');
        });

        it('gets typed constant value', function (): void {
            $intConst = $this->classWrapper->constants['INT_CONST'];
            $arrayConst = $this->classWrapper->constants['ARRAY_CONST'];

            expect($intConst->reflection->getValue())->toBe(42)
                ->and($arrayConst->reflection->getValue())->toBe(['a', 'b', 'c']);
        });

        it('detects constant visibility', function (): void {
            $publicConst = $this->classWrapper->constants['PUBLIC_CONST'];
            $protectedConst = $this->classWrapper->constants['PROTECTED_CONST'];
            $privateConst = $this->classWrapper->constants['PRIVATE_CONST'];

            expect($publicConst->reflection->isPublic())->toBeTrue()
                ->and($publicConst->reflection->isProtected())->toBeFalse()
                ->and($protectedConst->reflection->isProtected())->toBeTrue()
                ->and($privateConst->reflection->isPrivate())->toBeTrue();
        });

        it('gets constant type (PHP 8.3+)', function (): void {
            $publicConst = $this->classWrapper->constants['PUBLIC_CONST'];
            $type = $publicConst->reflection->getType();

            // PUBLIC_CONST has 'string' type
            expect($type)->not->toBeNull()
                ->and((string) $type)->toBe('string');
        });

        it('generates signature without class name', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $signature = $constant->getSignature(withClassName: false);

            expect($signature)->toContain('public const')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('PUBLIC_CONST')
                ->and($signature)->toContain("'public_value'")
                ->and($signature)->not->toContain('ClassFixture');
        });

        it('generates signature with class name', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $signature = $constant->getSignature(withClassName: true);

            expect($signature)->toContain('ClassFixture::PUBLIC_CONST');
        });

        it('generates signature for array constant', function (): void {
            $constant = $this->classWrapper->constants['ARRAY_CONST'];
            $signature = $constant->getSignature(withClassName: false);

            expect($signature)->toContain('ARRAY_CONST')
                ->and($signature)->toContain('array');
        });

        it('has reference to parent class', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];

            expect($constant->parentWrapper)->toBe($this->classWrapper)
                ->and($constant->inDocParentWrapper)->not->toBeNull();
        });

        it('can get constant doc comment', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $docComment = $constant->reflection->getDocComment();

            expect($docComment)->toBeString()
                ->and($docComment)->toContain('A public string constant');
        });

        it('can get constant description from wrapper', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $description = $constant->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('public string constant');
        });
    });

    describe('Interface Constants', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(InterfaceFixture::class));
        });

        it('gets interface constants', function (): void {
            $constants = $this->classWrapper->constants;

            expect($constants)->toHaveKey('INTERFACE_CONST')
                ->and($constants)->toHaveKey('VERSION');
        });

        it('interface constants are public', function (): void {
            foreach ($this->classWrapper->constants as $constant) {
                expect($constant->reflection->isPublic())->toBeTrue();
            }
        });

        it('gets interface constant types', function (): void {
            $interfaceConst = $this->classWrapper->constants['INTERFACE_CONST'];
            $versionConst = $this->classWrapper->constants['VERSION'];

            expect((string) $interfaceConst->reflection->getType())->toBe('string')
                ->and((string) $versionConst->reflection->getType())->toBe('int');
        });
    });

    describe('Inherited Constants', function (): void {
        beforeEach(function (): void {
            $this->childWrapper = new ClassWrapper(new ReflectionClass(ChildClassFixture::class));
        });

        it('gets constants from child class', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('CHILD_CONST')
                ->and($constants['CHILD_CONST']->reflection->getValue())->toBe('child_value');
        });

        it('gets inherited constants from parent', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('BASE_CONST')
                ->and($constants['BASE_CONST']->reflection->getValue())->toBe('base_value');
        });

        it('gets inherited constants from interfaces', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('INTERFACE_CONST')
                ->and($constants)->toHaveKey('SECONDARY_CONST');
        });

        it('can filter local constants only', function (): void {
            $localConstants = $this->childWrapper->getAllConstants(local: true, nonLocal: false);

            expect($localConstants)->toHaveKey('CHILD_CONST')
                ->and($localConstants)->not->toHaveKey('BASE_CONST')
                ->and($localConstants)->not->toHaveKey('INTERFACE_CONST');
        });

        it('can filter inherited constants only', function (): void {
            $inheritedConstants = $this->childWrapper->getAllConstants(local: false, nonLocal: true);

            expect($inheritedConstants)->not->toHaveKey('CHILD_CONST')
                ->and($inheritedConstants)->toHaveKey('BASE_CONST');
        });

        it('identifies declaring class for inherited constant', function (): void {
            $baseConst = $this->childWrapper->constants['BASE_CONST'];

            expect($baseConst->declaringClass)->not->toBeNull()
                ->and($baseConst->declaringClass->shortName)->toBe('BaseClassFixture');
        });

        it('identifies declaring class for local constant', function (): void {
            $childConst = $this->childWrapper->constants['CHILD_CONST'];

            expect($childConst->isLocalTo($this->childWrapper))->toBeTrue();
        });
    });

    describe('Constant Filtering', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
        });

        it('filters public constants only', function (): void {
            $publicConstants = $this->classWrapper->getAllConstants(
                protected: false,
                private: false
            );

            foreach ($publicConstants as $constant) {
                expect($constant->reflection->isPublic())->toBeTrue();
            }
        });

        it('filters protected constants', function (): void {
            $protectedConstants = $this->classWrapper->getAllConstants(
                public: false,
                private: false
            );

            foreach ($protectedConstants as $constant) {
                expect($constant->reflection->isProtected())->toBeTrue();
            }

            expect($protectedConstants)->toHaveKey('PROTECTED_CONST');
        });

        it('gets all visibility levels', function (): void {
            $allConstants = $this->classWrapper->getAllConstants();

            expect($allConstants)->toHaveKey('PUBLIC_CONST')
                ->and($allConstants)->toHaveKey('PROTECTED_CONST')
                ->and($allConstants)->toHaveKey('PRIVATE_CONST');
        });

        it('gets API constants (public only)', function (): void {
            $apiConstants = $this->classWrapper->getAllApiConstants();

            foreach ($apiConstants as $constant) {
                expect($constant->reflection->isPublic())->toBeTrue();
            }
        });
    });
});

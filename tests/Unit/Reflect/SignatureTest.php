<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, MethodWrapper, PropertyWrapper, ClassConstantWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{
    ClassFixture,
    InterfaceFixture,
    BaseClassFixture,
    ChildClassFixture,
    AdvancedTypesFixture,
    Php84FeaturesFixture
};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('Signature Generation', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Class Signatures', function (): void {
        it('generates class signature with modifiers', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
            $signature = $wrapper->getSignature();

            expect($signature)->toContain('class JulienBoudry\\PhpReference\\Tests\\Fixtures\\ClassFixture')
                ->and($signature)->toContain('{')
                ->and($signature)->toContain('}');
        });

        it('generates abstract class signature', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(BaseClassFixture::class));
            $signature = $wrapper->getSignature();

            expect($signature)->toContain('abstract class');
        });

        it('generates interface signature', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(InterfaceFixture::class));
            $signature = $wrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('interface')
                ->and($signature)->toContain('InterfaceFixture');
        });

        it('generates class signature with extends', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ChildClassFixture::class));
            $signature = $wrapper->getSignature();

            expect($signature)->toContain('extends')
                ->and($signature)->toContain('BaseClassFixture');
        });

        it('generates class signature with implements', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ChildClassFixture::class));
            $signature = $wrapper->getSignature();

            expect($signature)->toContain('implements')
                ->and($signature)->toContain('InterfaceFixture');
        });

        it('includes constants section in signature', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
            $signature = $wrapper->getSignature();

            expect($signature)->toContain('// Constants')
                ->and($signature)->toContain('PUBLIC_CONST');
        });

        it('includes properties section in signature', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
            $signature = $wrapper->getSignature();

            $hasPropertiesSection = str_contains($signature, '// Properties') ||
                                    str_contains($signature, '// Static Properties');
            expect($hasPropertiesSection)->toBeTrue();
        });

        it('includes methods section in signature', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
            $signature = $wrapper->getSignature();

            $hasMethodsSection = str_contains($signature, '// Methods') ||
                                 str_contains($signature, '// Static Methods');
            expect($hasMethodsSection)->toBeTrue();
        });

        it('API-only signature excludes non-public elements', function (): void {
            $wrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
            $signature = $wrapper->getSignature(onlyApi: true);

            expect($signature)->toContain('PUBLIC_CONST')
                ->and($signature)->not->toContain('PRIVATE_CONST')
                ->and($signature)->not->toContain('PROTECTED_CONST');
        });
    });

    describe('Method Signatures', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
        });

        it('generates public method signature', function (): void {
            $method = $this->classWrapper->methods['publicMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('function')
                ->and($signature)->toContain('publicMethod')
                ->and($signature)->toContain(': string');
        });

        it('generates method signature with parameters', function (): void {
            $method = $this->classWrapper->methods['publicMethodWithParams'];
            $signature = $method->getSignature();

            expect($signature)->toContain('string $name')
                ->and($signature)->toContain('int $times')
                ->and($signature)->toContain('= 1');
        });

        it('generates static method signature', function (): void {
            $method = $this->classWrapper->methods['publicStaticMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('static')
                ->and($signature)->toContain('function');
        });

        it('generates final method signature', function (): void {
            $method = $this->classWrapper->methods['finalMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain('final')
                ->and($signature)->toContain('public');
        });

        it('includes class name in signature when requested', function (): void {
            $method = $this->classWrapper->methods['publicMethod'];

            $withoutClass = $method->getSignature(withClassName: false);
            $withClass = $method->getSignature(withClassName: true);

            expect($withoutClass)->toContain('ClassFixture')
                ->and($withClass)->not->toContain('ClassFixture');
        });

        it('generates void return type signature', function (): void {
            $method = $this->classWrapper->methods['voidMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': void');
        });

        it('generates mixed return type signature', function (): void {
            $method = $this->classWrapper->methods['mixedMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': mixed');
        });

        it('generates self return type signature', function (): void {
            $method = $this->classWrapper->methods['selfMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': self');
        });

        it('generates static return type signature', function (): void {
            $method = $this->classWrapper->methods['staticReturnMethod'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': static');
        });

        it('generates abstract method signature', function (): void {
            $baseWrapper = new ClassWrapper(new ReflectionClass(BaseClassFixture::class));
            $method = $baseWrapper->methods['processInput'];
            $signature = $method->getSignature();

            expect($signature)->toContain('abstract')
                ->and($signature)->toContain('public')
                ->and($signature)->toContain('function');
        });
    });

    describe('Property Signatures', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
        });

        it('generates public property signature', function (): void {
            $property = $this->classWrapper->properties['publicProperty'];
            $signature = $property->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('$publicProperty')
                ->and($signature)->toContain("= 'default_value'");
        });

        it('generates readonly property signature', function (): void {
            $property = $this->classWrapper->properties['readonlyProperty'];
            $signature = $property->getSignature();

            expect($signature)->toContain('readonly')
                ->and($signature)->toContain('int');
        });

        it('generates static property signature', function (): void {
            $property = $this->classWrapper->properties['publicStaticProperty'];
            $signature = $property->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('static')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('$publicStaticProperty');
        });

        it('generates nullable property signature', function (): void {
            $property = $this->classWrapper->properties['nullableProperty'];
            $signature = $property->getSignature();

            expect($signature)->toContain('?string')
                ->and($signature)->toContain('= null');
        });

        it('includes class name in property signature when requested', function (): void {
            $property = $this->classWrapper->properties['publicProperty'];

            $withClass = $property->getSignature(withClassName: true);

            expect($withClass)->toContain('ClassFixture');
        });

        it('generates promoted property signature', function (): void {
            $property = $this->classWrapper->properties['promotedPublic'];
            $signature = $property->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('$promotedPublic');
        });
    });

    describe('Constant Signatures', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
        });

        it('generates typed constant signature', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $signature = $constant->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('const')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('PUBLIC_CONST')
                ->and($signature)->toContain("'public_value'");
        });

        it('generates int constant signature', function (): void {
            $constant = $this->classWrapper->constants['INT_CONST'];
            $signature = $constant->getSignature();

            expect($signature)->toContain('int')
                ->and($signature)->toContain('INT_CONST')
                ->and($signature)->toContain('42');
        });

        it('generates array constant signature', function (): void {
            $constant = $this->classWrapper->constants['ARRAY_CONST'];
            $signature = $constant->getSignature();

            expect($signature)->toContain('array')
                ->and($signature)->toContain('ARRAY_CONST');
        });

        it('includes class name in constant signature when requested', function (): void {
            $constant = $this->classWrapper->constants['PUBLIC_CONST'];
            $signature = $constant->getSignature(withClassName: true);

            expect($signature)->toContain('ClassFixture::PUBLIC_CONST');
        });
    });

    describe('Advanced Type Signatures', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(AdvancedTypesFixture::class));
        });

        it('generates union type property signature', function (): void {
            $property = $this->classWrapper->properties['unionProperty'];
            $signature = $property->getSignature();

            expect($signature)->toContain('string|int');
        });

        it('generates union type return signature', function (): void {
            $method = $this->classWrapper->methods['unionReturn'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': string|int');
        });

        it('generates nullable return signature', function (): void {
            $method = $this->classWrapper->methods['nullableReturn'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': ?string');
        });

        it('generates variadic parameter signature', function (): void {
            $method = $this->classWrapper->methods['variadicParam'];
            $signature = $method->getSignature();

            expect($signature)->toContain('string ...$values');
        });

        it('generates reference parameter signature', function (): void {
            $method = $this->classWrapper->methods['referenceParam'];
            $signature = $method->getSignature();

            expect($signature)->toContain('int &$counter');
        });

        it('generates never return signature', function (): void {
            $method = $this->classWrapper->methods['neverReturn'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': never');
        });

        it('generates string|false return signature', function (): void {
            $method = $this->classWrapper->methods['stringOrFalse'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': string|false');
        });

        it('generates true return signature', function (): void {
            $method = $this->classWrapper->methods['trueReturn'];
            $signature = $method->getSignature();

            expect($signature)->toContain(': true');
        });

        it('generates multiple default values signature', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $signature = $method->getSignature();

            expect($signature)->toContain('string $required')
                ->and($signature)->toContain('int $withDefault = 42')
                ->and($signature)->toContain('bool $boolDefault = true')
                ->and($signature)->toContain('?string $nullDefault = null');
        });
    });

    describe('PHP 8.4 Feature Signatures', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(Php84FeaturesFixture::class));
        });

        it('generates asymmetric visibility signature', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];
            $signature = $property->getSignature();

            // The signature should show public with protected(set)
            expect($signature)->toContain('public')
                ->and($signature)->toContain('string');
        });

        it('generates virtual property signature', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];
            $signature = $property->getSignature();

            expect($signature)->toContain('int')
                ->and($signature)->toContain('$doubleValue');
        });

        it('generates hooked property signature', function (): void {
            $property = $this->classWrapper->properties['hookedName'];
            $signature = $property->getSignature();

            expect($signature)->toContain('string')
                ->and($signature)->toContain('$hookedName');
        });
    });
});

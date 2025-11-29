<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, PropertyWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\Php84FeaturesFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('PHP 8.4 Features', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = new ClassWrapper(new ReflectionClass(Php84FeaturesFixture::class));
    });

    describe('Asymmetric Visibility', function (): void {
        it('detects public property with protected set', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];

            expect($property->reflection->isPublic())->toBeTrue();
        });

        it('detects public property with private set', function (): void {
            $property = $this->classWrapper->properties['publicPrivateSet'];

            expect($property->reflection->isPublic())->toBeTrue();
        });

        it('constructor promoted property with asymmetric visibility', function (): void {
            $property = $this->classWrapper->properties['constructorArg'];

            expect($property->reflection->isPublic())->toBeTrue()
                ->and($property->reflection->isPromoted())->toBeTrue();
        });
    });

    describe('Property Hooks', function (): void {
        it('detects hooked property', function (): void {
            $property = $this->classWrapper->properties['hookedName'];

            // hasHooks() is a PHP 8.4 method
            expect($property->reflection->hasHooks())->toBeTrue();
        });

        it('detects virtual property', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];

            expect($property->reflection->isVirtual())->toBeTrue()
                ->and($property->isVirtual())->toBeTrue();
        });

        it('computed property is virtual', function (): void {
            $property = $this->classWrapper->properties['computedLabel'];

            expect($property->isVirtual())->toBeTrue();
        });

        it('property with hooks on array', function (): void {
            $property = $this->classWrapper->properties['hookedItems'];

            expect($property->reflection->hasHooks())->toBeTrue()
                ->and((string) $property->reflection->getType())->toBe('array');
        });

        it('generates page path with virtual prefix', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];
            $path = $property->getPagePath();

            expect($path)->toContain('virtual_property_doubleValue.md');
        });

        it('non-virtual property has no virtual prefix in path', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];
            $path = $property->getPagePath();

            expect($path)->not->toContain('virtual_')
                ->and($path)->toContain('property_publicProtectedSet.md');
        });

        it('hooked non-virtual property has hooks but not virtual', function (): void {
            $property = $this->classWrapper->properties['hookedName'];

            expect($property->reflection->hasHooks())->toBeTrue()
                ->and($property->isVirtual())->toBeFalse();
        });
    });

    describe('Property Types with hooks', function (): void {
        it('virtual property has type', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];
            $type = $property->reflection->getType();

            expect($type)->not->toBeNull()
                ->and((string) $type)->toBe('int');
        });

        it('hooked property has type', function (): void {
            $property = $this->classWrapper->properties['hookedName'];
            $type = $property->reflection->getType();

            expect($type)->not->toBeNull()
                ->and((string) $type)->toBe('string');
        });

        it('computed label property has string type', function (): void {
            $property = $this->classWrapper->properties['computedLabel'];
            $type = $property->reflection->getType();

            expect((string) $type)->toBe('string');
        });
    });

    describe('Default values with new features', function (): void {
        it('property with asymmetric visibility has default', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];

            expect($property->reflection->hasDefaultValue())->toBeTrue()
                ->and($property->reflection->getDefaultValue())->toBe('default');
        });

        it('virtual property has no default value', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];

            // Virtual properties don't have default values
            expect($property->reflection->hasDefaultValue())->toBeFalse();
        });
    });

    describe('Methods in class with PHP 8.4 features', function (): void {
        it('has setName method', function (): void {
            $methods = $this->classWrapper->methods;

            expect($methods)->toHaveKey('setName');
        });

        it('setName method has string parameter', function (): void {
            $method = $this->classWrapper->methods['setName'];
            $params = $method->getParameters();

            expect($params)->toHaveCount(1)
                ->and($params[0]->name)->toBe('name')
                ->and((string) $params[0]->reflection->getType())->toBe('string');
        });

        it('getRawName returns string', function (): void {
            $method = $this->classWrapper->methods['getRawName'];
            $returnType = $method->getReturnType();

            expect((string) $returnType)->toBe('string');
        });

        it('has protected incrementCounter method', function (): void {
            $allMethods = $this->classWrapper->getAllUserDefinedMethods(protected: true);

            expect($allMethods)->toHaveKey('incrementCounter')
                ->and($allMethods['incrementCounter']->reflection->isProtected())->toBeTrue();
        });
    });

    describe('Signature generation for PHP 8.4 features', function (): void {
        it('generates signature for virtual property', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];
            $signature = $property->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('int')
                ->and($signature)->toContain('$doubleValue');
        });

        it('generates signature for hooked property', function (): void {
            $property = $this->classWrapper->properties['hookedName'];
            $signature = $property->getSignature();

            expect($signature)->toContain('public')
                ->and($signature)->toContain('string')
                ->and($signature)->toContain('$hookedName');
        });

        it('generates signature for asymmetric visibility property', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];
            $signature = $property->getSignature();

            expect($signature)->toContain('string')
                ->and($signature)->toContain('$publicProtectedSet');
        });
    });

    describe('Filtering virtual properties', function (): void {
        it('can identify all virtual properties', function (): void {
            $properties = $this->classWrapper->properties;
            $virtualProperties = array_filter(
                $properties,
                fn(PropertyWrapper $p) => $p->isVirtual()
            );

            expect($virtualProperties)->not->toBeEmpty()
                ->and($virtualProperties)->toHaveKey('doubleValue')
                ->and($virtualProperties)->toHaveKey('computedLabel');
        });

        it('can identify non-virtual properties', function (): void {
            $properties = $this->classWrapper->properties;
            $nonVirtualProperties = array_filter(
                $properties,
                fn(PropertyWrapper $p) => !$p->isVirtual()
            );

            expect($nonVirtualProperties)->toHaveKey('publicProtectedSet')
                ->and($nonVirtualProperties)->toHaveKey('hookedName');
        });
    });

    describe('Class signature with PHP 8.4 features', function (): void {
        it('generates full class signature', function (): void {
            $signature = $this->classWrapper->getSignature();

            expect($signature)->toContain('class')
                ->and($signature)->toContain('Php84FeaturesFixture')
                ->and($signature)->toContain('{')
                ->and($signature)->toContain('}');
        });

        it('signature includes properties section', function (): void {
            $signature = $this->classWrapper->getSignature();

            $hasPropertiesSection = str_contains($signature, '// Properties') ||
                                    str_contains($signature, '// Static Properties');
            expect($hasPropertiesSection)->toBeTrue();
        });
    });

    describe('DocBlock for PHP 8.4 properties', function (): void {
        it('virtual property has docblock', function (): void {
            $property = $this->classWrapper->properties['doubleValue'];
            $description = $property->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('virtual property');
        });

        it('asymmetric visibility property has docblock', function (): void {
            $property = $this->classWrapper->properties['publicProtectedSet'];
            $description = $property->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('protected set');
        });
    });
});

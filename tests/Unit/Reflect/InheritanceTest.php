<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{BaseClassFixture, ChildClassFixture, InterfaceFixture};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('Inheritance', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->baseWrapper = new ClassWrapper(new ReflectionClass(BaseClassFixture::class));
        $this->childWrapper = new ClassWrapper(new ReflectionClass(ChildClassFixture::class));
    });

    describe('Base Class', function (): void {
        it('is identified as abstract', function (): void {
            expect($this->baseWrapper->reflection->isAbstract())->toBeTrue();
        });

        it('has abstract method', function (): void {
            $method = $this->baseWrapper->methods['processInput'];

            expect($method->reflection->isAbstract())->toBeTrue();
        });

        it('has final method', function (): void {
            $method = $this->baseWrapper->methods['getFinalValue'];

            expect($method->reflection->isFinal())->toBeTrue();
        });

        it('has no parent class', function (): void {
            expect($this->baseWrapper->reflection->getParentClass())->toBeFalse();
        });
    });

    describe('Child Class', function (): void {
        it('is not abstract', function (): void {
            expect($this->childWrapper->reflection->isAbstract())->toBeFalse();
        });

        it('has parent class', function (): void {
            $parent = $this->childWrapper->reflection->getParentClass();

            expect($parent)->not->toBeFalse()
                ->and($parent->getName())->toBe(BaseClassFixture::class);
        });

        it('implements interfaces', function (): void {
            $interfaces = $this->childWrapper->reflection->getInterfaceNames();

            expect($interfaces)->toContain(InterfaceFixture::class);
        });

        it('uses traits', function (): void {
            $traits = $this->childWrapper->reflection->getTraitNames();

            expect($traits)->not->toBeEmpty()
                ->and($traits)->toContain('JulienBoudry\\PhpReference\\Tests\\Fixtures\\TraitFixture');
        });
    });

    describe('Inherited Methods', function (): void {
        it('child has its own methods', function (): void {
            $localMethods = $this->childWrapper->getAllUserDefinedMethods(local: true, nonLocal: false);

            expect($localMethods)->toHaveKey('childOnlyMethod')
                ->and($localMethods)->toHaveKey('processInput');
        });

        it('child inherits parent methods', function (): void {
            $methods = $this->childWrapper->methods;

            expect($methods)->toHaveKey('getBaseName')
                ->and($methods)->toHaveKey('getFinalValue');
        });

        it('child can filter local methods only', function (): void {
            $localMethods = $this->childWrapper->getAllUserDefinedMethods(local: true, nonLocal: false);

            // Should not include inherited methods from base class
            // but the overridden method is local
            expect($localMethods)->not->toHaveKey('getBaseName')
                ->and($localMethods)->not->toHaveKey('getFinalValue');
        });

        it('child can filter inherited methods only', function (): void {
            $inheritedMethods = $this->childWrapper->getAllUserDefinedMethods(local: false, nonLocal: true);

            expect($inheritedMethods)->toHaveKey('getBaseName')
                ->and($inheritedMethods)->not->toHaveKey('childOnlyMethod');
        });

        it('identifies method declaring class', function (): void {
            $getBaseNameMethod = $this->childWrapper->methods['getBaseName'];

            expect($getBaseNameMethod->declaringClass)->not->toBeNull()
                ->and($getBaseNameMethod->declaringClass->shortName)->toBe('BaseClassFixture');
        });

        it('identifies overridden method declaring class', function (): void {
            // getIdentifier is overridden in ChildClassFixture
            $getIdentifierMethod = $this->childWrapper->methods['getIdentifier'];

            expect($getIdentifierMethod->isLocalTo($this->childWrapper))->toBeTrue();
        });

        it('identifies local method', function (): void {
            $childOnlyMethod = $this->childWrapper->methods['childOnlyMethod'];

            expect($childOnlyMethod->isLocalTo($this->childWrapper))->toBeTrue()
                ->and($childOnlyMethod->declaringClass->shortName)->toBe('ChildClassFixture');
        });

        it('inDocParentWrapper points to declaring class if in API', function (): void {
            $getBaseNameMethod = $this->childWrapper->methods['getBaseName'];

            // If BaseClassFixture is in API, inDocParentWrapper should point to it
            expect($getBaseNameMethod->inDocParentWrapper)->not->toBeNull();
        });

        it('inherits interface methods', function (): void {
            $methods = $this->childWrapper->methods;

            expect($methods)->toHaveKey('getName')
                ->and($methods)->toHaveKey('setValue')
                ->and($methods)->toHaveKey('hasKey')
                ->and($methods)->toHaveKey('create');
        });

        it('inherits trait methods', function (): void {
            $methods = $this->childWrapper->methods;

            expect($methods)->toHaveKey('getTraitProperty')
                ->and($methods)->toHaveKey('setTraitProperty');
        });
    });

    describe('Inherited Properties', function (): void {
        it('child has its own properties', function (): void {
            $properties = $this->childWrapper->properties;

            expect($properties)->toHaveKey('childProperty');
        });

        it('child inherits parent properties', function (): void {
            $properties = $this->childWrapper->properties;

            expect($properties)->toHaveKey('basePublicProperty')
                ->and($properties)->toHaveKey('baseProtectedProperty');
        });

        it('child does not inherit private properties', function (): void {
            $properties = $this->childWrapper->properties;

            expect($properties)->not->toHaveKey('basePrivateProperty');
        });

        it('child can filter local properties only', function (): void {
            $localProperties = $this->childWrapper->getAllProperties(local: true, nonLocal: false);

            expect($localProperties)->toHaveKey('childProperty')
                ->and($localProperties)->not->toHaveKey('basePublicProperty');
        });

        it('child can filter inherited properties only', function (): void {
            $inheritedProperties = $this->childWrapper->getAllProperties(local: false, nonLocal: true);

            expect($inheritedProperties)->toHaveKey('basePublicProperty')
                ->and($inheritedProperties)->not->toHaveKey('childProperty');
        });

        it('identifies property declaring class', function (): void {
            $baseProperty = $this->childWrapper->properties['basePublicProperty'];

            expect($baseProperty->declaringClass)->not->toBeNull()
                ->and($baseProperty->declaringClass->shortName)->toBe('BaseClassFixture');
        });

        it('inherits trait properties', function (): void {
            $properties = $this->childWrapper->getAllProperties(protected: true);

            expect($properties)->toHaveKey('traitProperty');
        });
    });

    describe('Inherited Constants', function (): void {
        it('child has its own constants', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('CHILD_CONST');
        });

        it('child inherits parent constants', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('BASE_CONST');
        });

        it('child inherits interface constants', function (): void {
            $constants = $this->childWrapper->constants;

            expect($constants)->toHaveKey('INTERFACE_CONST')
                ->and($constants)->toHaveKey('VERSION');
        });

        it('child can filter local constants only', function (): void {
            $localConstants = $this->childWrapper->getAllConstants(local: true, nonLocal: false);

            expect($localConstants)->toHaveKey('CHILD_CONST')
                ->and($localConstants)->not->toHaveKey('BASE_CONST');
        });

        it('identifies constant declaring class', function (): void {
            $baseConst = $this->childWrapper->constants['BASE_CONST'];

            expect($baseConst->declaringClass)->not->toBeNull()
                ->and($baseConst->declaringClass->shortName)->toBe('BaseClassFixture');
        });
    });

    describe('API filtering with inheritance', function (): void {
        it('getAllApiMethods filters by API status', function (): void {
            $apiMethods = $this->childWrapper->getAllApiMethods();

            // All should be public and in API
            foreach ($apiMethods as $method) {
                expect($method->reflection->isPublic())->toBeTrue()
                    ->and($method->willBeInPublicApi)->toBeTrue();
            }
        });

        it('getAllApiProperties filters by API status', function (): void {
            $apiProperties = $this->childWrapper->getAllApiProperties();

            foreach ($apiProperties as $property) {
                expect($property->reflection->isPublic())->toBeTrue()
                    ->and($property->willBeInPublicApi)->toBeTrue();
            }
        });

        it('getAllApiConstants filters by API status', function (): void {
            $apiConstants = $this->childWrapper->getAllApiConstants();

            foreach ($apiConstants as $constant) {
                expect($constant->reflection->isPublic())->toBeTrue()
                    ->and($constant->willBeInPublicApi)->toBeTrue();
            }
        });

        it('can get local API methods only', function (): void {
            $localApiMethods = $this->childWrapper->getAllApiMethods(local: true, nonLocal: false);

            foreach ($localApiMethods as $method) {
                expect($method->isLocalTo($this->childWrapper))->toBeTrue();
            }
        });

        it('can get inherited API methods only', function (): void {
            $inheritedApiMethods = $this->childWrapper->getAllApiMethods(local: false, nonLocal: true);

            foreach ($inheritedApiMethods as $method) {
                expect($method->isLocalTo($this->childWrapper))->toBeFalse();
            }
        });
    });

    describe('Static vs Non-static filtering with inheritance', function (): void {
        it('can filter static methods', function (): void {
            $staticMethods = $this->childWrapper->getAllUserDefinedMethods(nonStatic: false);

            foreach ($staticMethods as $method) {
                expect($method->reflection->isStatic())->toBeTrue();
            }
        });

        it('can filter non-static methods', function (): void {
            $nonStaticMethods = $this->childWrapper->getAllUserDefinedMethods(static: false);

            foreach ($nonStaticMethods as $method) {
                expect($method->reflection->isStatic())->toBeFalse();
            }
        });

        it('can filter static properties', function (): void {
            // Child uses TraitFixture which has static property
            $staticProperties = $this->childWrapper->getAllProperties(
                protected: true,
                private: true,
                nonStatic: false
            );

            foreach ($staticProperties as $property) {
                expect($property->reflection->isStatic())->toBeTrue();
            }
        });

        it('can filter non-static properties', function (): void {
            $nonStaticProperties = $this->childWrapper->getAllProperties(
                protected: true,
                private: true,
                static: false
            );

            foreach ($nonStaticProperties as $property) {
                expect($property->reflection->isStatic())->toBeFalse();
            }
        });
    });
});

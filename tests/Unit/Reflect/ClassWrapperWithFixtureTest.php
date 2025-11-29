<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, MethodWrapper, PropertyWrapper, ClassConstantWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\ClassFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassWrapper with ClassFixture', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = new ClassWrapper(new ReflectionClass(ClassFixture::class));
    });

    describe('Basic class information', function (): void {
        it('wraps the class correctly', function (): void {
            expect($this->classWrapper->name)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures\\ClassFixture')
                ->and($this->classWrapper->shortName)->toBe('ClassFixture');
        });

        it('is user-defined', function (): void {
            expect($this->classWrapper->isUserDefined())->toBeTrue();
        });

        it('is not abstract', function (): void {
            expect($this->classWrapper->reflection->isAbstract())->toBeFalse();
        });

        it('is not an interface', function (): void {
            expect($this->classWrapper->reflection->isInterface())->toBeFalse();
        });

        it('is not a trait', function (): void {
            expect($this->classWrapper->reflection->isTrait())->toBeFalse();
        });

        it('is not an enum', function (): void {
            expect($this->classWrapper->reflection->isEnum())->toBeFalse();
        });

        it('has correct TYPE constant', function (): void {
            expect(ClassWrapper::TYPE)->toBe('class');
        });
    });

    describe('Page paths', function (): void {
        it('generates correct page path', function (): void {
            $path = $this->classWrapper->getPagePath();

            expect($path)->toContain('JulienBoudry/PhpReference/Tests/Fixtures/ClassFixture')
                ->and($path)->toEndWith('class_ClassFixture.md');
        });

        it('generates correct page directory', function (): void {
            $dir = $this->classWrapper->getPageDirectory();

            expect($dir)->toContain('JulienBoudry/PhpReference/Tests/Fixtures/ClassFixture')
                ->and($dir)->not->toEndWith('.md');
        });
    });

    describe('Methods', function (): void {
        it('has methods property', function (): void {
            expect($this->classWrapper->methods)->toBeArray()
                ->and($this->classWrapper->methods)->not->toBeEmpty();
        });

        it('contains expected public methods', function (): void {
            $methods = $this->classWrapper->methods;

            expect($methods)->toHaveKey('publicMethod')
                ->and($methods)->toHaveKey('publicMethodWithParams')
                ->and($methods)->toHaveKey('publicStaticMethod')
                ->and($methods)->toHaveKey('finalMethod');
        });

        it('contains protected methods', function (): void {
            $methods = $this->classWrapper->methods;

            expect($methods)->toHaveKey('protectedMethod')
                ->and($methods)->toHaveKey('protectedStaticMethod');
        });

        it('contains private methods', function (): void {
            $methods = $this->classWrapper->methods;

            expect($methods)->toHaveKey('privateMethod')
                ->and($methods)->toHaveKey('privateStaticMethod');
        });

        it('contains constructor', function (): void {
            $methods = $this->classWrapper->methods;

            expect($methods)->toHaveKey('__construct');
        });

        it('method values are MethodWrapper instances', function (): void {
            foreach ($this->classWrapper->methods as $method) {
                expect($method)->toBeInstanceOf(MethodWrapper::class);
            }
        });
    });

    describe('Properties', function (): void {
        it('has properties property', function (): void {
            expect($this->classWrapper->properties)->toBeArray()
                ->and($this->classWrapper->properties)->not->toBeEmpty();
        });

        it('contains expected public properties', function (): void {
            $properties = $this->classWrapper->properties;

            expect($properties)->toHaveKey('publicProperty')
                ->and($properties)->toHaveKey('readonlyProperty')
                ->and($properties)->toHaveKey('publicStaticProperty')
                ->and($properties)->toHaveKey('nullableProperty');
        });

        it('contains promoted properties', function (): void {
            $properties = $this->classWrapper->properties;

            expect($properties)->toHaveKey('promotedPublic')
                ->and($properties)->toHaveKey('promotedProtected');
        });

        it('property values are PropertyWrapper instances', function (): void {
            foreach ($this->classWrapper->properties as $property) {
                expect($property)->toBeInstanceOf(PropertyWrapper::class);
            }
        });
    });

    describe('Constants', function (): void {
        it('has constants property', function (): void {
            expect($this->classWrapper->constants)->toBeArray()
                ->and($this->classWrapper->constants)->not->toBeEmpty();
        });

        it('contains expected constants', function (): void {
            $constants = $this->classWrapper->constants;

            expect($constants)->toHaveKey('PUBLIC_CONST')
                ->and($constants)->toHaveKey('PROTECTED_CONST')
                ->and($constants)->toHaveKey('PRIVATE_CONST')
                ->and($constants)->toHaveKey('INT_CONST')
                ->and($constants)->toHaveKey('ARRAY_CONST');
        });

        it('constant values are ClassConstantWrapper instances', function (): void {
            foreach ($this->classWrapper->constants as $constant) {
                expect($constant)->toBeInstanceOf(ClassConstantWrapper::class);
            }
        });
    });

    describe('Filtering methods', function (): void {
        it('filters public methods only', function (): void {
            $publicMethods = $this->classWrapper->getAllUserDefinedMethods(
                protected: false,
                private: false
            );

            foreach ($publicMethods as $method) {
                expect($method->reflection->isPublic())->toBeTrue();
            }
        });

        it('filters protected methods only', function (): void {
            $protectedMethods = $this->classWrapper->getAllUserDefinedMethods(
                public: false,
                private: false
            );

            foreach ($protectedMethods as $method) {
                expect($method->reflection->isProtected())->toBeTrue();
            }
        });

        it('filters private methods only', function (): void {
            $privateMethods = $this->classWrapper->getAllUserDefinedMethods(
                public: false,
                protected: false
            );

            foreach ($privateMethods as $method) {
                expect($method->reflection->isPrivate())->toBeTrue();
            }
        });

        it('filters static methods only', function (): void {
            $staticMethods = $this->classWrapper->getAllUserDefinedMethods(nonStatic: false);

            foreach ($staticMethods as $method) {
                expect($method->reflection->isStatic())->toBeTrue();
            }

            expect($staticMethods)->toHaveKey('publicStaticMethod');
        });

        it('filters non-static methods only', function (): void {
            $nonStaticMethods = $this->classWrapper->getAllUserDefinedMethods(static: false);

            foreach ($nonStaticMethods as $method) {
                expect($method->reflection->isStatic())->toBeFalse();
            }

            expect($nonStaticMethods)->toHaveKey('publicMethod');
        });
    });

    describe('Filtering properties', function (): void {
        it('filters public properties only', function (): void {
            $publicProperties = $this->classWrapper->getAllProperties(
                protected: false,
                private: false
            );

            foreach ($publicProperties as $property) {
                expect($property->reflection->isPublic())->toBeTrue();
            }
        });

        it('filters static properties only', function (): void {
            $staticProperties = $this->classWrapper->getAllProperties(nonStatic: false);

            foreach ($staticProperties as $property) {
                expect($property->reflection->isStatic())->toBeTrue();
            }

            expect($staticProperties)->toHaveKey('publicStaticProperty');
        });

        it('filters non-static properties only', function (): void {
            $nonStaticProperties = $this->classWrapper->getAllProperties(static: false);

            foreach ($nonStaticProperties as $property) {
                expect($property->reflection->isStatic())->toBeFalse();
            }

            expect($nonStaticProperties)->toHaveKey('publicProperty');
        });
    });

    describe('getElementByName', function (): void {
        it('finds method by name', function (): void {
            $element = $this->classWrapper->getElementByName('publicMethod');

            expect($element)->toBeInstanceOf(MethodWrapper::class)
                ->and($element->name)->toBe('publicMethod');
        });

        it('finds property by name', function (): void {
            $element = $this->classWrapper->getElementByName('publicProperty');

            expect($element)->toBeInstanceOf(PropertyWrapper::class)
                ->and($element->name)->toBe('publicProperty');
        });

        it('finds constant by name', function (): void {
            $element = $this->classWrapper->getElementByName('PUBLIC_CONST');

            expect($element)->toBeInstanceOf(ClassConstantWrapper::class)
                ->and($element->name)->toBe('PUBLIC_CONST');
        });

        it('returns null for non-existent name', function (): void {
            $element = $this->classWrapper->getElementByName('nonExistent');

            expect($element)->toBeNull();
        });
    });

    describe('API filtering', function (): void {
        it('getAllApiMethods returns public API methods', function (): void {
            $apiMethods = $this->classWrapper->getAllApiMethods();

            foreach ($apiMethods as $method) {
                expect($method->reflection->isPublic())->toBeTrue();
            }
        });

        it('getAllApiProperties returns public API properties', function (): void {
            $apiProperties = $this->classWrapper->getAllApiProperties();

            foreach ($apiProperties as $property) {
                expect($property->reflection->isPublic())->toBeTrue();
            }
        });

        it('getAllApiConstants returns public API constants', function (): void {
            $apiConstants = $this->classWrapper->getAllApiConstants();

            foreach ($apiConstants as $constant) {
                expect($constant->reflection->isPublic())->toBeTrue();
            }
        });

        it('internal method is excluded from API', function (): void {
            $apiMethods = $this->classWrapper->getAllApiMethods();

            // internalMethod has @internal tag
            expect($apiMethods)->not->toHaveKey('internalMethod');
        });
    });

    describe('DocBlock', function (): void {
        it('has docBlock', function (): void {
            expect($this->classWrapper->docBlock)->not->toBeNull();
        });

        it('gets class description', function (): void {
            $description = $this->classWrapper->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('comprehensive fixture class');
        });

        it('gets class summary', function (): void {
            $summary = $this->classWrapper->getSummary();

            expect($summary)->not->toBeNull()
                ->and($summary)->toContain('comprehensive fixture class');
        });

        it('has @api tag', function (): void {
            expect($this->classWrapper->hasApiTag)->toBeTrue();
        });

        it('does not have @internal tag', function (): void {
            expect($this->classWrapper->hasInternalTag)->toBeFalse();
        });
    });

    describe('willBeInPublicApi', function (): void {
        it('class is in public API', function (): void {
            expect($this->classWrapper->willBeInPublicApi)->toBeTrue();
        });

        it('public method is in public API', function (): void {
            $method = $this->classWrapper->methods['publicMethod'];

            expect($method->willBeInPublicApi)->toBeTrue();
        });

        it('private method is not in public API', function (): void {
            $method = $this->classWrapper->methods['privateMethod'];

            expect($method->willBeInPublicApi)->toBeFalse();
        });

        it('internal method is not in public API', function (): void {
            $method = $this->classWrapper->methods['internalMethod'];

            expect($method->willBeInPublicApi)->toBeFalse();
        });
    });

    describe('Signature generation', function (): void {
        it('generates full class signature', function (): void {
            $signature = $this->classWrapper->getSignature();

            expect($signature)->toContain('class')
                ->and($signature)->toContain('ClassFixture')
                ->and($signature)->toContain('{')
                ->and($signature)->toContain('}');
        });

        it('signature contains constants', function (): void {
            $signature = $this->classWrapper->getSignature();

            expect($signature)->toContain('PUBLIC_CONST');
        });

        it('signature contains properties', function (): void {
            $signature = $this->classWrapper->getSignature();

            // Signature should contain at least one property
            $hasProperty = str_contains($signature, 'publicProperty') ||
                           str_contains($signature, 'publicStaticProperty');
            expect($hasProperty)->toBeTrue();
        });

        it('signature contains methods', function (): void {
            $signature = $this->classWrapper->getSignature();

            expect($signature)->toContain('publicMethod');
        });

        it('API-only signature excludes private elements', function (): void {
            $signature = $this->classWrapper->getSignature(onlyApi: true);

            expect($signature)->not->toContain('PRIVATE_CONST')
                ->and($signature)->not->toContain('privateMethod');
        });
    });

    describe('Modifier names', function (): void {
        it('gets modifier names for class', function (): void {
            $modifiers = $this->classWrapper->getModifierNames();

            // ClassFixture is not abstract, not final
            expect($modifiers)->toBe('');
        });
    });
});

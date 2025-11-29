<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{TraitWrapper, ClassWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\{TraitFixture, SecondaryTraitFixture};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('TraitWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Primary Trait', function (): void {
        beforeEach(function (): void {
            $this->traitWrapper = new TraitWrapper(new ReflectionClass(TraitFixture::class));
        });

        it('wraps a trait correctly', function (): void {
            expect($this->traitWrapper->name)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures\\TraitFixture')
                ->and($this->traitWrapper->shortName)->toBe('TraitFixture');
        });

        it('identifies as a trait', function (): void {
            expect($this->traitWrapper->reflection->isTrait())->toBeTrue();
        });

        it('has correct TYPE constant', function (): void {
            expect(TraitWrapper::TYPE)->toBe('trait');
        });

        it('generates correct page path with trait type', function (): void {
            $path = $this->traitWrapper->getPagePath();

            expect($path)->toContain('TraitFixture')
                ->and($path)->toContain('trait_TraitFixture.md');
        });

        it('has trait methods', function (): void {
            $methods = $this->traitWrapper->methods;

            expect($methods)->toHaveKey('getTraitProperty')
                ->and($methods)->toHaveKey('setTraitProperty')
                ->and($methods)->toHaveKey('protectedTraitMethod')
                ->and($methods)->toHaveKey('getStaticTraitCounter');
        });

        it('has trait properties', function (): void {
            $properties = $this->traitWrapper->properties;

            expect($properties)->toHaveKey('traitProperty')
                ->and($properties)->toHaveKey('staticTraitProperty');
        });

        it('detects protected property', function (): void {
            $property = $this->traitWrapper->properties['traitProperty'];

            expect($property->reflection->isProtected())->toBeTrue()
                ->and($property->reflection->isPublic())->toBeFalse();
        });

        it('detects static property', function (): void {
            $staticProp = $this->traitWrapper->properties['staticTraitProperty'];

            expect($staticProp->reflection->isStatic())->toBeTrue();
        });

        it('detects static method', function (): void {
            $staticMethod = $this->traitWrapper->methods['getStaticTraitCounter'];

            expect($staticMethod->reflection->isStatic())->toBeTrue();
        });

        it('detects protected method', function (): void {
            $protectedMethod = $this->traitWrapper->methods['protectedTraitMethod'];

            expect($protectedMethod->reflection->isProtected())->toBeTrue();
        });

        it('generates signature correctly', function (): void {
            $signature = $this->traitWrapper->getSignature();

            // Signature uses fully qualified class name
            expect($signature)->toContain('trait')
                ->and($signature)->toContain('TraitFixture')
                ->and($signature)->toContain('traitProperty')
                ->and($signature)->toContain('getTraitProperty');
        });

        it('can filter methods by visibility', function (): void {
            $publicMethods = $this->traitWrapper->getAllUserDefinedMethods(protected: false, private: false);
            $allMethods = $this->traitWrapper->getAllUserDefinedMethods();

            expect(\count($publicMethods))->toBeLessThan(\count($allMethods));

            foreach ($publicMethods as $method) {
                expect($method->reflection->isPublic())->toBeTrue();
            }
        });

        it('can filter properties by visibility', function (): void {
            $publicProperties = $this->traitWrapper->getAllProperties(protected: false, private: false);
            $allProperties = $this->traitWrapper->getAllProperties();

            // Trait has no public properties, so public filter should return empty
            expect($publicProperties)->toBeEmpty()
                ->and($allProperties)->not->toBeEmpty();
        });
    });

    describe('Secondary Trait', function (): void {
        beforeEach(function (): void {
            $this->traitWrapper = new TraitWrapper(new ReflectionClass(SecondaryTraitFixture::class));
        });

        it('wraps the secondary trait', function (): void {
            expect($this->traitWrapper->shortName)->toBe('SecondaryTraitFixture');
        });

        it('has the toggle method', function (): void {
            $methods = $this->traitWrapper->methods;

            expect($methods)->toHaveKey('toggleSecondaryFlag');
        });

        it('has private property', function (): void {
            $properties = $this->traitWrapper->getAllProperties(protected: true, private: true);

            expect($properties)->toHaveKey('secondaryFlag')
                ->and($properties['secondaryFlag']->reflection->isPrivate())->toBeTrue();
        });
    });

    describe('ClassWrapper compatibility with traits', function (): void {
        it('ClassWrapper can wrap a trait', function (): void {
            $classWrapper = new ClassWrapper(new ReflectionClass(TraitFixture::class));

            expect($classWrapper->reflection->isTrait())->toBeTrue()
                ->and($classWrapper->shortName)->toBe('TraitFixture');
        });

        it('ClassWrapper gets trait methods', function (): void {
            $classWrapper = new ClassWrapper(new ReflectionClass(TraitFixture::class));
            $methods = $classWrapper->methods;

            expect($methods)->toHaveKey('getTraitProperty');
        });

        it('TraitWrapper extends ClassWrapper', function (): void {
            $traitWrapper = new TraitWrapper(new ReflectionClass(TraitFixture::class));

            expect($traitWrapper)->toBeInstanceOf(ClassWrapper::class);
        });
    });
});

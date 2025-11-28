<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, PropertyWrapper, MethodWrapper, ClassConstantWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\SeeTagFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ReflectionWrapper @see tag resolution', function (): void {
    beforeEach(function (): void {
        // Use the Fixtures namespace to index our test class
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = new ClassWrapper(new ReflectionClass(SeeTagFixture::class));
    });

    it('resolves @see tag referencing a static property', function (): void {
        $method = $this->classWrapper->methods['methodReferencingStaticProperty'];

        expect($method)->toBeInstanceOf(MethodWrapper::class);

        $seeTags = $method->getResolvedSeeTags();

        expect($seeTags)->not->toBeNull()
            ->and($seeTags)->toBeArray()
            ->and($seeTags)->not->toBeEmpty();

        $firstTag = reset($seeTags);
        expect($firstTag['destination'])->toBeInstanceOf(PropertyWrapper::class)
            ->and($firstTag['destination']->name)->toBe('staticProperty');
    });

    it('resolves @see tag referencing a regular property', function (): void {
        $method = $this->classWrapper->methods['methodReferencingRegularProperty'];

        expect($method)->toBeInstanceOf(MethodWrapper::class);

        $seeTags = $method->getResolvedSeeTags();

        expect($seeTags)->not->toBeNull()
            ->and($seeTags)->toBeArray()
            ->and($seeTags)->not->toBeEmpty();

        $firstTag = reset($seeTags);
        expect($firstTag['destination'])->toBeInstanceOf(PropertyWrapper::class)
            ->and($firstTag['destination']->name)->toBe('regularProperty');
    });

    it('resolves @see tag referencing a constant', function (): void {
        $method = $this->classWrapper->methods['methodReferencingConstant'];

        expect($method)->toBeInstanceOf(MethodWrapper::class);

        $seeTags = $method->getResolvedSeeTags();

        expect($seeTags)->not->toBeNull()
            ->and($seeTags)->toBeArray()
            ->and($seeTags)->not->toBeEmpty();

        $firstTag = reset($seeTags);
        expect($firstTag['destination'])->toBeInstanceOf(ClassConstantWrapper::class)
            ->and($firstTag['destination']->name)->toBe('STATIC_CONSTANT');
    });

    it('resolves @see tag referencing a method', function (): void {
        $method = $this->classWrapper->methods['methodReferencingMethod'];

        expect($method)->toBeInstanceOf(MethodWrapper::class);

        $seeTags = $method->getResolvedSeeTags();

        expect($seeTags)->not->toBeNull()
            ->and($seeTags)->toBeArray()
            ->and($seeTags)->not->toBeEmpty();

        $firstTag = reset($seeTags);
        expect($firstTag['destination'])->toBeInstanceOf(MethodWrapper::class)
            ->and($firstTag['destination']->name)->toBe('methodReferencingStaticProperty');
    });

    it('resolves @see tag referencing a method with short syntax (without class name)', function (): void {
        $method = $this->classWrapper->methods['methodReferencingMethodShort'];

        expect($method)->toBeInstanceOf(MethodWrapper::class);

        $seeTags = $method->getResolvedSeeTags();

        expect($seeTags)->not->toBeNull()
            ->and($seeTags)->toBeArray()
            ->and($seeTags)->not->toBeEmpty();

        $firstTag = reset($seeTags);
        expect($firstTag['destination'])->toBeInstanceOf(MethodWrapper::class)
            ->and($firstTag['destination']->name)->toBe('methodReferencingStaticProperty');
    });
});

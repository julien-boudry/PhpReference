<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Log\{ErrorCollector, ErrorLevel};
use JulienBoudry\PhpReference\Reflect\{ClassWrapper, MethodWrapper};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassWrapper', function (): void {
    beforeEach(function (): void {
        // Initialize Execution for tests that use reflection
        $this->execution = createExecutionFixture();
    });

    it('wraps a class correctly', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));

        expect($wrapper->name)->toBe('JulienBoudry\\PhpReference\\Log\\ErrorCollector')
            ->and($wrapper->shortName)->toBe('ErrorCollector');
    });

    it('identifies itself as a class', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));

        expect($wrapper->reflection->isUserDefined())->toBeTrue()
            ->and($wrapper->reflection->isInterface())->toBeFalse();
    });

    it('can identify an enum', function (): void {
        $wrapper = new ClassWrapper(new ReflectionEnum(ErrorLevel::class));

        expect($wrapper->reflection->isEnum())->toBeTrue();
    });

    it('gets public methods only by default', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $methods = $wrapper->getAllUserDefinedMethods();

        expect($methods)->not->toBeEmpty();

        foreach ($methods as $method) {
            expect($method)->toBeInstanceOf(MethodWrapper::class)
                ->and($method->reflection->isPublic())->toBeTrue();
        }
    });

    it('can get a specific method by name', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $method = $wrapper->methods['addWarning'] ?? null;

        expect($method)->toBeInstanceOf(MethodWrapper::class)
            ->and($method->name)->toBe('addWarning');
    });

    it('returns null for non-existent method', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $method = $wrapper->getElementByName('nonExistentMethod');

        expect($method)->toBeNull();
    });

    it('gets public properties', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $properties = $wrapper->properties;

        expect($properties)->toBeArray();
    });

    it('detects if class is in public API', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));

        // ErrorCollector is public, so should be in API with IsPubliclyAccessible
        expect($wrapper->willBeInPublicApi)->toBeTrue();
    });

    it('generates correct page path', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $path = $wrapper->getPagePath();

        expect($path)->toContain('JulienBoudry/PhpReference/Log/ErrorCollector')
            ->and($path)->toEndWith('class_ErrorCollector.md');
    });

    it('generates correct page directory', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $dir = $wrapper->getPageDirectory();

        expect($dir)->toContain('JulienBoudry/PhpReference/Log/ErrorCollector')
            ->and($dir)->not->toEndWith('.md');
    });

    it('can get element by name (method)', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $element = $wrapper->getElementByName('addWarning');

        expect($element)->toBeInstanceOf(MethodWrapper::class)
            ->and($element->name)->toBe('addWarning');
    });

    it('can get constants from enum', function (): void {
        $wrapper = new ClassWrapper(new ReflectionEnum(ErrorLevel::class));
        $constants = $wrapper->constants;

        expect($constants)->toBeArray()
            ->and($constants)->not->toBeEmpty();
    });

    it('detects non-abstract classes', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));

        // ErrorCollector is not abstract
        expect($wrapper->reflection->isAbstract())->toBeFalse();
    });

    it('detects final classes', function (): void {
        $wrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));

        // ErrorCollector is not final
        expect($wrapper->reflection->isFinal())->toBeFalse();
    });
});

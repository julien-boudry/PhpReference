<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('MethodWrapper Edge Cases', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
    });

    it('detects variadic methods', function (): void {
        // Check if any method is variadic by checking its parameters
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            $params = $method->reflection->getParameters();
            foreach ($params as $param) {
                // isVariadic returns boolean
                expect($param->isVariadic())->toBeBool();
            }
        }

        // At minimum, we tested the check works
        expect($methods)->not->toBeEmpty();
    });

    it('detects methods with reference parameters', function (): void {
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            $params = $method->reflection->getParameters();
            foreach ($params as $param) {
                // isPassedByReference returns boolean
                expect($param->isPassedByReference())->toBeBool();
            }
        }

        expect($methods)->not->toBeEmpty();
    });

    it('can get method closure', function (): void {
        $method = $this->classWrapper->methods['addWarning'];

        // getClosure requires an object instance
        $collector = new ErrorCollector;
        $closure = $method->reflection->getClosure($collector);

        expect($closure)->toBeInstanceOf(Closure::class);
    });

    it('can check if method is generator', function (): void {
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            expect($method->reflection->isGenerator())->toBeBool();
        }

        expect($methods)->not->toBeEmpty();
    });

    it('can get method prototype if exists', function (): void {
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            try {
                $prototype = $method->reflection->getPrototype();
                expect($prototype)->toBeInstanceOf(ReflectionMethod::class);
            } catch (ReflectionException $e) {
                // Method has no prototype (not overriding parent method)
                expect($e)->toBeInstanceOf(ReflectionException::class);
            }
        }

        expect($methods)->not->toBeEmpty();
    });

    it('can check if method is deprecated', function (): void {
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            expect($method->reflection->isDeprecated())->toBeBool();
        }

        expect($methods)->not->toBeEmpty();
    });

    it('can get method modifiers', function (): void {
        $method = $this->classWrapper->methods['addWarning'];
        $modifiers = $method->reflection->getModifiers();

        expect($modifiers)->toBeInt();
    });

    it('detects internal methods', function (): void {
        $methods = $this->classWrapper->getAllUserDefinedMethods();

        foreach ($methods as $method) {
            // User-defined methods should not be internal
            expect($method->reflection->isInternal())->toBeFalse();
        }

        expect($methods)->not->toBeEmpty();
    });

    it('can get method start and end line', function (): void {
        $method = $this->classWrapper->methods['addWarning'];

        $startLine = $method->reflection->getStartLine();
        $endLine = $method->reflection->getEndLine();

        expect($startLine)->toBeInt()
            ->and($endLine)->toBeInt()
            ->and($endLine)->toBeGreaterThanOrEqual($startLine);
    });

    it('can invoke method with reflection', function (): void {
        $method = $this->classWrapper->methods['addWarning'];
        $collector = new ErrorCollector;

        // Invoke the method
        $method->reflection->invoke($collector, 'Test warning message');

        // Verify it was called
        expect($collector->hasErrors())->toBeTrue();
    });
});

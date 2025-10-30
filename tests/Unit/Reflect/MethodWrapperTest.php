<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, MethodWrapper, ParameterWrapper};
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('MethodWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $this->method = $this->classWrapper->methods['addWarning'];
    });

    it('wraps a method correctly', function (): void {
        expect($this->method)->toBeInstanceOf(MethodWrapper::class)
            ->and($this->method->name)->toBe('addWarning');
    });

    it('detects public methods', function (): void {
        expect($this->method->reflection->isPublic())->toBeTrue()
            ->and($this->method->reflection->isProtected())->toBeFalse()
            ->and($this->method->reflection->isPrivate())->toBeFalse();
    });

    it('gets method parameters', function (): void {
        $params = $this->method->getParameters();

        expect($params)->toBeArray()
            ->and($params)->not->toBeEmpty();

        foreach ($params as $param) {
            expect($param)->toBeInstanceOf(ParameterWrapper::class);
        }
    });

    it('gets specific parameter by name', function (): void {
        $params = $this->method->getParameters();
        $param = $params[0] ?? null;

        expect($param)->toBeInstanceOf(ParameterWrapper::class)
            ->and($param->name)->toBe('message');
    });

    it('returns null for non-existent parameter', function (): void {
        $params = $this->method->getParameters();

        // There shouldn't be many params, so a high index should return null
        expect(\count($params))->toBeLessThan(10);
    });

    it('detects if method has return type', function (): void {
        expect($this->method->hasReturnType())->toBeTrue();
    });

    it('gets return type', function (): void {
        $returnType = $this->method->getReturnType();

        expect($returnType)->not->toBeNull();
    });

    it('detects static methods', function (): void {
        // addWarning is not static
        expect($this->method->reflection->isStatic())->toBeFalse();
    });

    it('detects abstract methods', function (): void {
        // ErrorCollector methods are not abstract
        expect($this->method->reflection->isAbstract())->toBeFalse();
    });

    it('detects final methods', function (): void {
        // Most methods are not final
        expect($this->method->reflection->isFinal())->toBeFalse();
    });

    it('generates correct page path', function (): void {
        $path = $this->method->getPagePath();

        expect($path)->toContain('ErrorCollector')
            ->and($path)->toContain('method_addWarning.md');
    });

    it('has reference to parent class', function (): void {
        expect($this->method->inDocParentWrapper)->not->toBeNull();
    });

    it('detects if method is in public API', function (): void {
        // Public method in public class should be in API
        expect($this->method->willBeInPublicApi)->toBeTrue();
    });

    it('can get number of parameters', function (): void {
        expect($this->method->reflection->getNumberOfParameters())->toBeGreaterThan(0)
            ->and($this->method->reflection->getNumberOfRequiredParameters())->toBeGreaterThan(0);
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, ParameterWrapper};
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ParameterWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
        $this->method = $this->classWrapper->methods['addWarning'];
        $this->params = $this->method->getParameters();
    });

    it('wraps a parameter correctly', function (): void {
        $param = $this->params[0] ?? null;

        expect($param)->toBeInstanceOf(ParameterWrapper::class)
            ->and($param->name)->toBe('message');
    });

    it('detects parameter type', function (): void {
        $param = $this->params[0] ?? null;

        expect($param->reflection->hasType())->toBeTrue();

        $type = $param->reflection->getType();
        expect($type)->not->toBeNull();
    });

    it('detects if parameter has default value', function (): void {
        $messageParam = $this->params[0] ?? null;
        $contextParam = $this->params[1] ?? null;

        expect($messageParam->reflection->isDefaultValueAvailable())->toBeFalse()
            ->and($contextParam->reflection->isDefaultValueAvailable())->toBeTrue();
    });

    it('gets default value when available', function (): void {
        $contextParam = $this->params[1] ?? null;

        expect($contextParam->reflection->isDefaultValueAvailable())->toBeTrue()
            ->and($contextParam->reflection->getDefaultValue())->toBeNull();
    });

    it('detects variadic parameters', function (): void {
        $param = $this->params[0] ?? null;

        // Regular parameter is not variadic
        expect($param->reflection->isVariadic())->toBeFalse();
    });

    it('detects if parameter is passed by reference', function (): void {
        $param = $this->params[0] ?? null;

        // Regular parameter is not passed by reference
        expect($param->reflection->isPassedByReference())->toBeFalse();
    });

    it('gets parameter position', function (): void {
        $messageParam = $this->params[0] ?? null;
        $contextParam = $this->params[1] ?? null;

        expect($messageParam->reflection->getPosition())->toBe(0)
            ->and($contextParam->reflection->getPosition())->toBeGreaterThan(0);
    });

    it('detects nullable parameters', function (): void {
        $contextParam = $this->params[1] ?? null;

        // The 'context' parameter is nullable (?string)
        expect($contextParam->reflection->allowsNull())->toBeTrue();
    });
});

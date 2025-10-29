<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Exception\UnresolvableReferenceException;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('CodeIndex Integration', function (): void {
    beforeEach(function (): void {
        // Initialize Execution::$instance for tests that use wrappers
        $this->execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        $this->index = $this->execution->codeIndex;
    });

    it('discovers classes in namespace', function (): void {
        expect($this->index->elementsList)->not->toBeEmpty()
            ->and($this->index->elementsList)->toBeArray();
    });

    it('creates namespace wrappers', function (): void {
        expect($this->index->namespaces)->not->toBeEmpty()
            ->and($this->index->namespaces)->toHaveKey('JulienBoudry\\PhpReference\\Log');
    });

    it('can get a class wrapper by name', function (): void {
        $className = array_key_first($this->index->elementsList);
        $wrapper = $this->index->getClassWrapper($className);

        expect($wrapper)->not->toBeNull()
            ->and($wrapper->name)->toBe($className);
    });

    it('returns null for non-existent class', function (): void {
        $wrapper = $this->index->getClassWrapper('NonExistent\\Class');
        expect($wrapper)->toBeNull();
    });

    it('filters API classes correctly', function (): void {
        $apiClasses = $this->index->getApiClasses();
        expect($apiClasses)->toBeArray();

        foreach ($apiClasses as $class) {
            expect($class->willBeInPublicApi)->toBeTrue();
        }
    });

    it('throws exception for invalid element path format', function (): void {
        expect(fn() => $this->index->getElement('InvalidFormat'))
            ->toThrow(UnresolvableReferenceException::class);
    });

    it('throws exception for non-existent class in element path', function (): void {
        expect(fn() => $this->index->getElement('NonExistent\\Class::method'))
            ->toThrow(UnresolvableReferenceException::class);
    });
});

describe('CodeIndex with test fixtures', function (): void {
    it('can index JulienBoudry\\PhpReference\\Log namespace', function (): void {
        // Initialize Execution for this test with a smaller namespace
        $execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        $index = $execution->codeIndex;

        expect($index->elementsList)->not->toBeEmpty()
            ->and($index->namespaces)->not->toBeEmpty();

        // Should contain Log classes
        $classNames = array_keys($index->elementsList);
        expect($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorCollector')
            ->and($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorLevel');
    });

    it('builds namespace hierarchy correctly', function (): void {
        $execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        $index = $execution->codeIndex;
        $namespace = $index->namespaces['JulienBoudry\\PhpReference\\Log'];

        expect($namespace->hierarchy)->toBeArray()
            ->and($namespace->hierarchy)->not->toBeEmpty();

        // Should have parent namespaces in hierarchy
        expect(\count($namespace->hierarchy))->toBeGreaterThan(0);
    });
});

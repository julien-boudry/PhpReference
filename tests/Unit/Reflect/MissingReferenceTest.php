<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Log\ErrorLevel;
use JulienBoudry\PhpReference\Tests\Fixtures\MissingReferenceFixture;
use JulienBoudry\PhpReference\Tests\Fixtures\ExternalDependencyFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('Missing Reference Logging', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = $this->execution->codeIndex->getClassWrapper(MissingReferenceFixture::class);
    });

    it('logs a warning when @throws references a non-existent class in the indexed namespace', function (): void {
        // Get the method that references a missing exception
        $method = $this->classWrapper->methods['methodWithMissingThrows'];

        // Clear any existing errors
        $this->execution->errorCollector->clear();

        // This should trigger a warning
        $resolved = $method->getResolvedThrowsTags();

        // The resolved array should still have the entry, but with null destination
        expect($resolved)->toBeArray();
        expect($resolved)->toHaveCount(1);
        expect($resolved[0]['destination'])->toBeNull();

        // Check that a warning was logged
        expect($this->execution->errorCollector->hasErrors(ErrorLevel::WARNING))->toBeTrue();
        $warnings = $this->execution->errorCollector->getErrors(ErrorLevel::WARNING);
        expect($warnings)->toHaveCount(1);
        expect($warnings[0]->message)->toContain('not found in indexed namespace');
        expect($warnings[0]->message)->toContain('NonExistentFixtureException');
    });

    it('logs a warning when @throws references a missing class with full namespace path', function (): void {
        // Get the method that references a missing exception with full namespace
        $method = $this->classWrapper->methods['methodWithFullNamespaceMissingThrows'];

        // Clear any existing errors
        $this->execution->errorCollector->clear();

        // This should trigger a warning
        $resolved = $method->getResolvedThrowsTags();

        // The resolved array should still have the entry, but with null destination
        expect($resolved)->toBeArray();
        expect($resolved)->toHaveCount(1);
        expect($resolved[0]['destination'])->toBeNull();

        // Check that a warning was logged
        expect($this->execution->errorCollector->hasErrors(ErrorLevel::WARNING))->toBeTrue();
        $warnings = $this->execution->errorCollector->getErrors(ErrorLevel::WARNING);
        expect($warnings)->toHaveCount(1);
        expect($warnings[0]->message)->toContain('AnotherMissingException');
        expect($warnings[0]->message)->toContain('not found in indexed namespace');
    });

    it('does not log a warning when @throws references an external class', function (): void {
        // Get a class that extends Exception and throws RuntimeException
        $externalDependencyClass = $this->execution->codeIndex->getClassWrapper(ExternalDependencyFixture::class);
        $method = $externalDependencyClass->methods['methodThatThrowsExternalException'];

        // Clear any existing errors
        $this->execution->errorCollector->clear();

        // This should NOT trigger a warning (RuntimeException is external)
        $resolved = $method->getResolvedThrowsTags();

        // The resolved array should have the entry with null destination (external class)
        expect($resolved)->toBeArray();
        expect($resolved)->toHaveCount(1);
        expect($resolved[0]['destination'])->toBeNull();

        // No warning should be logged for external classes
        expect($this->execution->errorCollector->hasErrors(ErrorLevel::WARNING))->toBeFalse();
    });
});

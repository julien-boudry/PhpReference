<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('MethodPageWriter', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        // Get wrapper from CodeIndex to have declaringNamespace properly initialized
        $classWrapper = $this->execution->codeIndex->elementsList[ErrorCollector::class];
        $this->methodWrapper = $classWrapper->methods['addWarning'];
        $this->writer = new MethodPageWriter($this->methodWrapper);
    });

    it('can be created', function (): void {
        expect($this->writer)->toBeInstanceOf(MethodPageWriter::class);
    });

    it('generates correct file path', function (): void {
        $path = $this->methodWrapper->getPagePath();

        expect($path)->toBeString()
            ->and($path)->toContain('ErrorCollector')
            ->and($path)->toContain('method_addWarning.md');
    });

    it('generates markdown content', function (): void {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains method name', function (): void {
        $content = $this->writer->makeContent();

        expect($content)->toContain('addWarning');
    });

    it('content contains parameters section when method has parameters', function (): void {
        $content = $this->writer->makeContent();

        // addWarning() method has parameters
        expect($content)->toContain('Parameter');
    });

    it('content contains return type information', function (): void {
        $content = $this->writer->makeContent();

        // addWarning() method has return type
        expect($content)->toContain('Return');
    });

    it('generates valid markdown format', function (): void {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#\s+/m');
    });

    it('content includes method signature', function (): void {
        $content = $this->writer->makeContent();

        // Should show the method signature
        expect($content)->toContain('(');
    });

    it('content includes parameter names', function (): void {
        $content = $this->writer->makeContent();

        // addWarning() has $message parameter
        expect($content)->toContain('message');
    });
});

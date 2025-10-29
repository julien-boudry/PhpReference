<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('MethodPageWriter', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture();
        // Get wrapper from CodeIndex to have declaringNamespace properly initialized
        $classWrapper = $this->execution->codeIndex->elementsList[\JulienBoudry\PhpReference\Log\ErrorCollector::class];
        $this->methodWrapper = $classWrapper->methods['addWarning'];
        $this->writer = new MethodPageWriter($this->methodWrapper);
    });

    it('can be created', function () {
        expect($this->writer)->toBeInstanceOf(MethodPageWriter::class);
    });

    it('generates correct file path', function () {
        $path = $this->methodWrapper->getPagePath();

        expect($path)->toBeString()
            ->and($path)->toContain('ErrorCollector')
            ->and($path)->toContain('method_addWarning.md');
    });

    it('generates markdown content', function () {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains method name', function () {
        $content = $this->writer->makeContent();

        expect($content)->toContain('addWarning');
    });

    it('content contains parameters section when method has parameters', function () {
        $content = $this->writer->makeContent();

        // addWarning() method has parameters
        expect($content)->toContain('Parameter');
    });

    it('content contains return type information', function () {
        $content = $this->writer->makeContent();

        // addWarning() method has return type
        expect($content)->toContain('Return');
    });

    it('generates valid markdown format', function () {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#\s+/m');
    });

    it('content includes method signature', function () {
        $content = $this->writer->makeContent();

        // Should show the method signature
        expect($content)->toContain('(');
    });

    it('content includes parameter names', function () {
        $content = $this->writer->makeContent();

        // addWarning() has $message parameter
        expect($content)->toContain('message');
    });
});

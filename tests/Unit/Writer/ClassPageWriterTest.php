<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassPageWriter', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        // Get wrapper from CodeIndex to have declaringNamespace properly initialized
        $this->wrapper = $this->execution->codeIndex->elementsList[ErrorCollector::class];
        $this->writer = new ClassPageWriter($this->wrapper);
    });

    it('can be created', function (): void {
        expect($this->writer)->toBeInstanceOf(ClassPageWriter::class);
    });

    it('generates correct file path', function (): void {
        $path = $this->wrapper->getPagePath();

        expect($path)->toBeString()
            ->and($path)->toContain('JulienBoudry/PhpReference/Log/ErrorCollector')
            ->and($path)->toEndWith('class_ErrorCollector.md');
    });

    it('generates markdown content', function (): void {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains class name', function (): void {
        $content = $this->writer->makeContent();

        expect($content)->toContain('ErrorCollector');
    });

    it('content contains methods section when class has methods', function (): void {
        $content = $this->writer->makeContent();

        // ErrorCollector has methods, so should have Methods section
        expect($content)->toContain('Method');
    });

    it('content contains namespace information', function (): void {
        $content = $this->writer->makeContent();

        expect($content)->toContain('JulienBoudry\\PhpReference\\Log');
    });

    it('generates valid markdown format', function (): void {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#\s+/m');
    });

    it('content includes class signature', function (): void {
        $content = $this->writer->makeContent();

        // Should mention it's a class
        expect($content)->toContain('class');
    });
});

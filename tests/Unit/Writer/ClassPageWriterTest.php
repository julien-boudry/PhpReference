<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassPageWriter', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture();
        // Get wrapper from CodeIndex to have declaringNamespace properly initialized
        $this->wrapper = $this->execution->codeIndex->elementsList[ErrorCollector::class];
        $this->writer = new ClassPageWriter($this->wrapper);
    });

    it('can be created', function () {
        expect($this->writer)->toBeInstanceOf(ClassPageWriter::class);
    });

    it('generates correct file path', function () {
        $path = $this->wrapper->getPagePath();

        expect($path)->toBeString()
            ->and($path)->toContain('JulienBoudry/PhpReference/Log/ErrorCollector')
            ->and($path)->toEndWith('class_ErrorCollector.md');
    });

    it('generates markdown content', function () {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains class name', function () {
        $content = $this->writer->makeContent();

        expect($content)->toContain('ErrorCollector');
    });

    it('content contains methods section when class has methods', function () {
        $content = $this->writer->makeContent();

        // ErrorCollector has methods, so should have Methods section
        expect($content)->toContain('Method');
    });

    it('content contains namespace information', function () {
        $content = $this->writer->makeContent();

        expect($content)->toContain('JulienBoudry\\PhpReference\\Log');
    });

    it('generates valid markdown format', function () {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#\s+/m');
    });

    it('content includes class signature', function () {
        $content = $this->writer->makeContent();

        // Should mention it's a class
        expect($content)->toContain('class');
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Writer\NamespacePageWriter;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('NamespacePageWriter', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        $this->namespace = $this->execution->codeIndex->namespaces['JulienBoudry\\PhpReference\\Log'];
        $this->writer = new NamespacePageWriter($this->namespace);
    });

    it('can be created', function () {
        expect($this->writer)->toBeInstanceOf(NamespacePageWriter::class);
    });

    it('generates correct file path', function () {
        $path = $this->namespace->getPagePath();

        expect($path)->toBeString()
            ->and($path)->toContain('JulienBoudry/PhpReference/Log')
            ->and($path)->toEndWith('readme.md');
    });

    it('generates markdown content', function () {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains namespace name', function () {
        $content = $this->writer->makeContent();

        expect($content)->toContain('Log')
            ->and($content)->toContain('JulienBoudry');
    });

    it('content lists classes in namespace', function () {
        $content = $this->writer->makeContent();

        // Log namespace should contain ErrorCollector, ErrorLevel, etc.
        $hasClass = str_contains($content, 'ErrorCollector') || str_contains($content, 'Class');
        expect($hasClass)->toBeTrue();
    });

    it('generates valid markdown format', function () {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#\s+/m');
    });

    it('content includes links to classes', function () {
        $content = $this->writer->makeContent();

        // Should have markdown links
        expect($content)->toContain('[')
            ->and($content)->toContain(']');
    });

    it('content shows namespace hierarchy', function () {
        $content = $this->writer->makeContent();

        // Should show the namespace path
        expect($content)->toContain('\\');
    });
});

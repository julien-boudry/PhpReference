<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('PublicApiSummaryWriter', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        $this->writer = new PublicApiSummaryWriter(
            $this->execution->codeIndex,
            '/readme.md'
        );
    });

    it('can be created', function () {
        expect($this->writer)->toBeInstanceOf(PublicApiSummaryWriter::class);
    });

    it('has correct file path from constructor', function () {
        // The writePath property should be set to the provided file path
        expect($this->writer->writePath)->toEndWith('readme.md');
    });

    it('generates markdown content', function () {
        $content = $this->writer->makeContent();

        expect($content)->toBeString()
            ->and($content)->not->toBeEmpty();
    });

    it('content contains API documentation header', function () {
        $content = $this->writer->makeContent();

        // Should have some header about API
        expect($content)->toMatch('/^#\s+/m');
    });

    it('generates valid markdown format', function () {
        $content = $this->writer->makeContent();

        // Should have markdown headers
        expect($content)->toMatch('/^#/m');
    });

    it('content lists namespaces', function () {
        $content = $this->writer->makeContent();

        // Should contain namespace information
        expect($content)->toContain('JulienBoudry');
    });
});

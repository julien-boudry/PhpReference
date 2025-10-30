<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\NamespaceWrapper;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('NamespaceWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
        // Get an existing namespace from the index
        $this->namespace = $this->execution->codeIndex->namespaces['JulienBoudry\\PhpReference\\Log'];
    });

    it('wraps a namespace correctly', function (): void {
        expect($this->namespace->name)->toBe('JulienBoudry\\PhpReference\\Log')
            ->and($this->namespace->shortName)->toBe('Log');
    });

    it('builds hierarchy correctly', function (): void {
        expect($this->namespace->hierarchy)->toBeArray()
            ->and($this->namespace->hierarchy)->not->toBeEmpty();

        // Should contain parent namespaces
        expect(\count($this->namespace->hierarchy))->toBeGreaterThan(0);
    });

    it('generates correct page path', function (): void {
        $path = $this->namespace->getPagePath();

        expect($path)->toContain('JulienBoudry/PhpReference/Log')
            ->and($path)->toEndWith('readme.md');
    });

    it('generates correct page directory', function (): void {
        $dir = $this->namespace->getPageDirectory();

        expect($dir)->toContain('JulienBoudry/PhpReference/Log')
            ->and($dir)->not->toEndWith('.md');
    });

    it('has classes property', function (): void {
        expect($this->namespace->classes)->toBeArray();
    });

    it('hierarchy contains namespace wrappers and strings', function (): void {
        foreach ($this->namespace->hierarchy as $item) {
            // Each item should be either a NamespaceWrapper or a string
            expect($item instanceof NamespaceWrapper || \is_string($item))->toBeTrue();
        }
    });

    it('has non-empty classes array for populated namespace', function (): void {
        // Log namespace should have ErrorCollector, ErrorLevel, CollectedError
        expect($this->namespace->classes)->not->toBeEmpty();
    });
});

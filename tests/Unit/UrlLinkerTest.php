<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\{ApiSummaryPage, UrlLinker};

describe('UrlLinker', function (): void {
    it('can be created with a writable source page', function (): void {
        $sourcePage = new ApiSummaryPage;
        $linker = new UrlLinker($sourcePage);

        expect($linker)->toBeInstanceOf(UrlLinker::class)
            ->and($linker->sourcePage)->toBe($sourcePage);
    });

    it('generates correct relative link from root to root', function (): void {
        // Both files are siblings in the root directory
        // Source is in "/" directory, dest is "/other.md"
        $source = new ApiSummaryPage('/readme.md');
        $dest = new ApiSummaryPage('/other.md');
        $linker = new UrlLinker($source);

        $link = $linker->to($dest);

        // When both are in root ("/"), the link is just the filename
        expect($link)->toBe('other.md');
    });

    it('generates correct relative link from deep path to root', function (): void {
        $source = new class implements JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface {
            public function getPageDirectory(): string
            {
                return '/ref/Namespace/Class';
            }

            public function getPagePath(): string
            {
                return '/ref/Namespace/Class/method.md';
            }

            public function getUrlLinker(): UrlLinker
            {
                return new UrlLinker($this);
            }
        };

        $dest = new ApiSummaryPage('/readme.md');
        $linker = new UrlLinker($source);

        $link = $linker->to($dest);

        expect($link)->toBe('../../../readme.md');
    });

    it('generates correct relative link between sibling pages', function (): void {
        // Two classes in the same namespace
        // ClassA directory: /ref/Namespace/ClassA, path: /ref/Namespace/ClassA/ClassA.md
        // ClassB directory: /ref/Namespace/ClassB, path: /ref/Namespace/ClassB/ClassB.md
        // From ClassA directory to ClassB file: ../ClassB/ClassB.md
        $source = new class implements JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface {
            public function getPageDirectory(): string
            {
                return '/ref/Namespace/ClassA';
            }

            public function getPagePath(): string
            {
                return '/ref/Namespace/ClassA/ClassA.md';
            }

            public function getUrlLinker(): UrlLinker
            {
                return new UrlLinker($this);
            }
        };

        $dest = new class implements JulienBoudry\PhpReference\Reflect\Capabilities\WritableInterface {
            public function getPageDirectory(): string
            {
                return '/ref/Namespace/ClassB';
            }

            public function getPagePath(): string
            {
                return '/ref/Namespace/ClassB/ClassB.md';
            }

            public function getUrlLinker(): UrlLinker
            {
                return new UrlLinker($this);
            }
        };

        $linker = new UrlLinker($source);
        $link = $linker->to($dest);

        expect($link)->toBe('../ClassB/ClassB.md');
    });
});

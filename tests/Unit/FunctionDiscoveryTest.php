<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\FunctionDiscovery;

describe('FunctionDiscovery', function (): void {
    it('discovers standalone functions in a namespace', function (): void {
        // Require the test functions file
        require_once __DIR__ . '/../Fixtures/TestFunctions.php';

        $functions = FunctionDiscovery::getFunctionsInNamespace('JulienBoudry\\PhpReference\\Tests\\Fixtures');

        expect($functions)->toBeArray()
            ->and($functions)->toContain('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction')
            ->and($functions)->toContain('JulienBoudry\\PhpReference\\Tests\\Fixtures\\anotherTestFunction');
    });

    it('returns empty array for namespace without functions', function (): void {
        $functions = FunctionDiscovery::getFunctionsInNamespace('NonExistent\\Namespace');

        expect($functions)->toBeArray()
            ->and($functions)->toBeEmpty();
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\{App, CodeIndex, Execution};
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

describe('Full Documentation Generation', function () {
    it('can create an Execution instance', function () {
        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: sys_get_temp_dir() . '/php-reference-test',
            publicApiDefinition: new IsPubliclyAccessible(),
        );

        expect($execution)->toBeInstanceOf(Execution::class)
            ->and($execution->codeIndex)->toBeInstanceOf(CodeIndex::class);
    });

    it('CodeIndex discovers classes in namespace', function () {
        $codeIndex = new CodeIndex('JulienBoudry\\PhpReference\\Log');
        
        expect($codeIndex->elementsList)->not->toBeEmpty()
            ->and($codeIndex->elementsList)->toBeArray();
    });

    it('CodeIndex includes expected classes', function () {
        $codeIndex = new CodeIndex('JulienBoudry\\PhpReference\\Log');
        $classNames = array_keys($codeIndex->elementsList);
        
        expect($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorCollector')
            ->and($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorLevel');
    });

    it('Execution has correct output directory', function () {
        $outputDir = sys_get_temp_dir() . '/php-reference-test-' . uniqid();
        
        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: $outputDir,
            publicApiDefinition: new IsPubliclyAccessible(),
        );

        expect($execution->outputDir)->toBe($outputDir);
    });

    it('Execution uses correct API definition', function () {
        $apiDefinition = new IsPubliclyAccessible();
        
        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: sys_get_temp_dir(),
            publicApiDefinition: $apiDefinition,
        );

        expect($execution->publicApiDefinition)->toBe($apiDefinition);
    });
});
<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\{CodeIndex, Config, Execution};
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

describe('Full Documentation Generation', function (): void {
    it('can create an Execution instance', function (): void {
        $config = new Config;
        $config->set('api', new IsPubliclyAccessible);

        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: sys_get_temp_dir() . '/php-reference-test',
            config: $config,
        );

        expect($execution)->toBeInstanceOf(Execution::class)
            ->and($execution->codeIndex)->toBeInstanceOf(CodeIndex::class);
    });

    it('CodeIndex discovers classes in namespace', function (): void {
        $codeIndex = new CodeIndex('JulienBoudry\\PhpReference\\Log');

        expect($codeIndex->elementsList)->not->toBeEmpty()
            ->and($codeIndex->elementsList)->toBeArray();
    });

    it('CodeIndex includes expected classes', function (): void {
        $codeIndex = new CodeIndex('JulienBoudry\\PhpReference\\Log');
        $classNames = array_keys($codeIndex->elementsList);

        expect($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorCollector')
            ->and($classNames)->toContain('JulienBoudry\\PhpReference\\Log\\ErrorLevel');
    });

    it('Execution has correct output directory', function (): void {
        $outputDir = sys_get_temp_dir() . '/php-reference-test-' . uniqid();

        $config = new Config;
        $config->set('api', new IsPubliclyAccessible);

        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: $outputDir,
            config: $config,
        );

        expect($execution->outputDir)->toBe($outputDir);
    });

    it('Execution uses correct API definition', function (): void {
        $apiDefinition = new IsPubliclyAccessible;

        $config = new Config;
        $config->set('api', $apiDefinition);

        $execution = new Execution(
            codeIndex: new CodeIndex('JulienBoudry\\PhpReference\\Log'),
            outputDir: sys_get_temp_dir(),
            config: $config,
        );

        expect($execution->publicApiDefinition)->toBe($apiDefinition);
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Config;

use function JulienBoudry\PhpReference\Tests\{createTempConfig, removeTempConfig};

describe('Config with file', function (): void {
    afterEach(function (): void {
        if (isset($this->tempConfigPath)) {
            removeTempConfig($this->tempConfigPath);
        }
    });

    it('loads config from file', function (): void {
        $this->tempConfigPath = createTempConfig([
            'namespace' => 'My\\Test\\Namespace',
            'output' => '/tmp/output',
            'api' => 'HasTagApi',
        ]);

        $config = new Config($this->tempConfigPath);

        expect($config->get('namespace'))->toBe('My\\Test\\Namespace')
            ->and($config->get('output'))->toBe('/tmp/output')
            ->and($config->get('api'))->toBe('HasTagApi');
    });

    it('handles missing config file gracefully', function (): void {
        $config = new Config('/non/existent/config.php');
        expect($config)->toBeInstanceOf(Config::class);
    });

    it('CLI args override config file values', function (): void {
        $this->tempConfigPath = createTempConfig([
            'namespace' => 'Original\\Namespace',
            'output' => '/original/path',
        ]);

        $config = new Config($this->tempConfigPath);
        $config->mergeWithCliArgs([
            'namespace' => 'Overridden\\Namespace',
        ]);

        expect($config->get('namespace'))->toBe('Overridden\\Namespace')
            ->and($config->get('output'))->toBe('/original/path');
    });
});

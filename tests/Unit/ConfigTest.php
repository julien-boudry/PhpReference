<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Config;
use JulienBoudry\PhpReference\Definition\{HasTagApi, IsPubliclyAccessible};
use JulienBoudry\PhpReference\Exception\InvalidConfigurationException;

describe('Config', function (): void {
    it('can be created without a config file', function (): void {
        $config = new Config('/non/existent/path');

        expect($config)->toBeInstanceOf(Config::class);
    });

    it('can set and get values', function (): void {
        $config = new Config;
        $config->set('test', 'value');

        expect($config->get('test'))->toBe('value');
    });

    it('returns default value when key does not exist', function (): void {
        $config = new Config;

        expect($config->get('nonexistent', 'default'))->toBe('default');
    });

    it('can check if key exists', function (): void {
        $config = new Config;
        $config->set('existing', 'value');

        expect($config->has('existing'))->toBeTrue()
            ->and($config->has('nonexistent'))->toBeFalse();
    });

    it('can return all config as array', function (): void {
        $config = new Config;
        $config->set('key1', 'value1');
        $config->set('key2', 'value2');

        $all = $config->all();

        expect($all)->toBeArray()
            ->and($all)->toHaveKey('key1')
            ->and($all)->toHaveKey('key2')
            ->and($all['key1'])->toBe('value1');
    });

    it('can merge with CLI args', function (): void {
        $config = new Config;
        $config->set('namespace', 'Original');

        $config->mergeWithCliArgs([
            'namespace' => 'Updated',
            'output' => '/path/to/output',
        ]);

        expect($config->get('namespace'))->toBe('Updated')
            ->and($config->get('output'))->toBe('/path/to/output');
    });

    it('ignores null values when merging CLI args', function (): void {
        $config = new Config;
        $config->set('namespace', 'Original');

        $config->mergeWithCliArgs([
            'namespace' => null,
            'output' => '/path',
        ]);

        expect($config->get('namespace'))->toBe('Original')
            ->and($config->get('output'))->toBe('/path');
    });

    describe('API definition resolution', function (): void {
        it('resolves IsPubliclyAccessible from string', function (): void {
            $config = new Config;
            $config->set('api', 'IsPubliclyAccessible');

            $definition = $config->getApiDefinition();

            expect($definition)->toBeInstanceOf(IsPubliclyAccessible::class);
        });

        it('resolves HasTagApi from string case-insensitive', function (): void {
            $config = new Config;
            $config->set('api', 'hastagapi');

            $definition = $config->getApiDefinition();

            expect($definition)->toBeInstanceOf(HasTagApi::class);
        });

        it('returns existing instance if already set', function (): void {
            $config = new Config;
            $instance = new IsPubliclyAccessible;
            $config->set('api', $instance);

            $definition = $config->getApiDefinition();

            expect($definition)->toBe($instance);
        });

        it('throws exception for unknown API definition', function (): void {
            $config = new Config;
            $config->set('api', 'UnknownDefinition');

            expect(fn() => $config->getApiDefinition())
                ->toThrow(InvalidConfigurationException::class);
        });

        it('returns default when not set', function (): void {
            $config = new Config;
            $default = new HasTagApi;

            $definition = $config->getApiDefinition($default);

            // When 'api' key is not set, getApiDefinition uses the default passed to get()
            // which returns the $default instance
            expect($definition)->toBeInstanceOf(HasTagApi::class);
        });
    });
});

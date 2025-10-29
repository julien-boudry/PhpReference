<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Navigation;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('Navigation', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture('JulienBoudry\\PhpReference\\Log');
    });

    it('generates breadcrumb for namespace', function () {
        $namespace = $this->execution->codeIndex->namespaces['JulienBoudry\\PhpReference\\Log'];
        $breadcrumb = Navigation::getBreadcrumb($namespace);

        expect($breadcrumb)->toBeString()
            ->and($breadcrumb)->not->toBeEmpty();
    });

    it('breadcrumb contains namespace hierarchy', function () {
        $namespace = $this->execution->codeIndex->namespaces['JulienBoudry\\PhpReference\\Log'];
        $breadcrumb = Navigation::getBreadcrumb($namespace);

        // Should contain backslashes for hierarchy
        expect($breadcrumb)->toContain('\\');
    });

    it('generates breadcrumb for class', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $breadcrumb = Navigation::getBreadcrumb($class);

        expect($breadcrumb)->toBeString()
            ->and($breadcrumb)->not->toBeEmpty();
    });

    it('breadcrumb for class includes namespace', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $breadcrumb = Navigation::getBreadcrumb($class);

        // Should contain backslashes for namespace hierarchy
        expect($breadcrumb)->toContain('\\')
            ->and($breadcrumb)->toContain('ErrorCollector');
    });

    it('breadcrumb for class shows class name in bold', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $breadcrumb = Navigation::getBreadcrumb($class);

        // Class name should be bolded
        expect($breadcrumb)->toContain('**ErrorCollector**');
    });

    it('generates breadcrumb for method', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $method = $class->methods['addWarning'];
        $breadcrumb = Navigation::getBreadcrumb($method);

        expect($breadcrumb)->toBeString()
            ->and($breadcrumb)->not->toBeEmpty();
    });

    it('breadcrumb for method includes class link', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $method = $class->methods['addWarning'];
        $breadcrumb = Navigation::getBreadcrumb($method);

        // Should have markdown link to parent class
        expect($breadcrumb)->toContain('[ErrorCollector]');
    });

    it('breadcrumb format includes backslashes as separators', function () {
        $class = $this->execution->codeIndex->elementsList['JulienBoudry\\PhpReference\\Log\\ErrorCollector'];
        $breadcrumb = Navigation::getBreadcrumb($class);

        // Should use backslashes as separators
        expect($breadcrumb)->toContain('\\');
    });
});

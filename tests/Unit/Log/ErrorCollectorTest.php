<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Log\{CollectedError, ErrorCollector, ErrorLevel};

describe('ErrorCollector', function () {
    beforeEach(function () {
        $this->collector = new ErrorCollector();
    });

    it('starts with no errors', function () {
        expect($this->collector->hasErrors())->toBeFalse()
            ->and($this->collector->getErrorCount())->toBe(0);
    });

    it('can add a warning', function () {
        $this->collector->addWarning('Test warning');

        expect($this->collector->hasErrors())->toBeTrue()
            ->and($this->collector->getErrorCount())->toBe(1)
            ->and($this->collector->getErrorCount(ErrorLevel::WARNING))->toBe(1);
    });

    it('can add multiple errors of different levels', function () {
        $this->collector->addWarning('Warning 1');
        $this->collector->addWarning('Warning 2');
        $this->collector->addNotice('Notice 1');
        $this->collector->addError('Error 1', ErrorLevel::ERROR);

        expect($this->collector->getErrorCount())->toBe(4)
            ->and($this->collector->getErrorCount(ErrorLevel::WARNING))->toBe(2)
            ->and($this->collector->getErrorCount(ErrorLevel::NOTICE))->toBe(1)
            ->and($this->collector->getErrorCount(ErrorLevel::ERROR))->toBe(1);
    });

    it('can filter errors by level', function () {
        $this->collector->addWarning('Warning');
        $this->collector->addNotice('Notice');
        $this->collector->addError('Error', ErrorLevel::ERROR);

        $warnings = $this->collector->getErrors(ErrorLevel::WARNING);
        $notices = $this->collector->getErrors(ErrorLevel::NOTICE);

        expect($warnings)->toHaveCount(1)
            ->and($notices)->toHaveCount(1)
            ->and($warnings[0]->message)->toBe('Warning')
            ->and($notices[0]->message)->toBe('Notice');
    });

    it('can get summary of errors', function () {
        $this->collector->addWarning('W1');
        $this->collector->addWarning('W2');
        $this->collector->addNotice('N1');

        $summary = $this->collector->getSummary();

        expect($summary)->toHaveKey('warning')
            ->and($summary)->toHaveKey('notice')
            ->and($summary['warning'])->toBe(2)
            ->and($summary['notice'])->toBe(1);
    });

    it('can clear all errors', function () {
        $this->collector->addWarning('Test');
        $this->collector->addError('Error', ErrorLevel::ERROR);

        expect($this->collector->getErrorCount())->toBe(2);

        $this->collector->clear();

        expect($this->collector->hasErrors())->toBeFalse()
            ->and($this->collector->getErrorCount())->toBe(0);
    });

    it('formats errors for console output', function () {
        $this->collector->addWarning('Test warning', 'Test context');

        $output = $this->collector->formatForConsole();

        expect($output)->toContain('Error Report')
            ->and($output)->toContain('Test warning')
            ->and($output)->toContain('Test context')
            ->and($output)->toContain('⚠️  WARNINGS');
    });

    it('stores timestamp with each error', function () {
        $before = new DateTimeImmutable();
        $this->collector->addWarning('Test');
        $after = new DateTimeImmutable();

        $errors = $this->collector->getErrors();
        $timestamp = $errors[0]->timestamp;

        expect($timestamp)->toBeInstanceOf(DateTimeImmutable::class)
            ->and($timestamp >= $before)->toBeTrue()
            ->and($timestamp <= $after)->toBeTrue();
    });
});

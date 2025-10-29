<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Exception\{
    InvalidConfigurationException,
    PhpReferenceException,
    UnresolvableReferenceException,
    UnsupportedOperationException
};

describe('Exceptions', function () {
    describe('PhpReferenceException', function () {
        it('can be thrown and caught', function () {
            expect(fn() => throw new PhpReferenceException('Test'))
                ->toThrow(PhpReferenceException::class, 'Test');
        });

        it('is an instance of Exception', function () {
            $exception = new PhpReferenceException('Test');
            expect($exception)->toBeInstanceOf(Exception::class);
        });
    });

    describe('UnresolvableReferenceException', function () {
        it('stores the reference', function () {
            $exception = new UnresolvableReferenceException(
                reference: 'MyClass',
                message: 'Class not found'
            );

            expect($exception->reference)->toBe('MyClass')
                ->and($exception->getMessage())->toContain('Class not found');
        });

        it('has a default message', function () {
            $exception = new UnresolvableReferenceException(reference: 'MyClass');

            expect($exception->getMessage())->toContain('Unable to resolve reference')
                ->and($exception->getMessage())->toContain('MyClass');
        });

        it('can chain previous exceptions', function () {
            $previous = new Exception('Original error');
            $exception = new UnresolvableReferenceException(
                reference: 'MyClass',
                previous: $previous
            );

            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('UnsupportedOperationException', function () {
        it('stores operation and wrapper type', function () {
            $exception = new UnsupportedOperationException(
                operation: 'getUrlLinker',
                wrapperType: 'ParameterWrapper'
            );

            expect($exception->operation)->toBe('getUrlLinker')
                ->and($exception->wrapperType)->toBe('ParameterWrapper')
                ->and($exception->getMessage())->toContain('getUrlLinker')
                ->and($exception->getMessage())->toContain('ParameterWrapper');
        });
    });

    describe('InvalidConfigurationException', function () {
        it('can be thrown with custom message', function () {
            expect(fn() => throw new InvalidConfigurationException('Bad config'))
                ->toThrow(InvalidConfigurationException::class, 'Bad config');
        });
    });
});

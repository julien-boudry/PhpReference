<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Exception\{
    InvalidConfigurationException,
    PhpReferenceException,
    UnresolvableReferenceException,
    UnsupportedOperationException
};

describe('Exceptions', function (): void {
    describe('PhpReferenceException', function (): void {
        it('can be thrown and caught', function (): void {
            expect(fn() => throw new PhpReferenceException('Test'))
                ->toThrow(PhpReferenceException::class, 'Test');
        });

        it('is an instance of Exception', function (): void {
            $exception = new PhpReferenceException('Test');
            expect($exception)->toBeInstanceOf(Exception::class);
        });
    });

    describe('UnresolvableReferenceException', function (): void {
        it('stores the reference', function (): void {
            $exception = new UnresolvableReferenceException(
                reference: 'MyClass',
                message: 'Class not found'
            );

            expect($exception->reference)->toBe('MyClass')
                ->and($exception->getMessage())->toContain('Class not found');
        });

        it('has a default message', function (): void {
            $exception = new UnresolvableReferenceException(reference: 'MyClass');

            expect($exception->getMessage())->toContain('Unable to resolve reference')
                ->and($exception->getMessage())->toContain('MyClass');
        });

        it('can chain previous exceptions', function (): void {
            $previous = new Exception('Original error');
            $exception = new UnresolvableReferenceException(
                reference: 'MyClass',
                previous: $previous
            );

            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('UnsupportedOperationException', function (): void {
        it('stores operation and wrapper type', function (): void {
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

    describe('InvalidConfigurationException', function (): void {
        it('can be thrown with custom message', function (): void {
            expect(fn() => throw new InvalidConfigurationException('Bad config'))
                ->toThrow(InvalidConfigurationException::class, 'Bad config');
        });
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Util;

describe('Util', function () {
    describe('arrayToString', function () {
        it('converts empty array', function () {
            expect(Util::arrayToString([]))->toBe('[]');
        });

        it('converts simple indexed array', function () {
            $result = Util::arrayToString([1, 2, 3]);
            expect($result)->toBe('[1, 2, 3]');
        });

        it('converts associative array', function () {
            $result = Util::arrayToString(['name' => 'John', 'age' => 30]);
            expect($result)->toContain("'name' => 'John'")
                ->and($result)->toContain("'age' => 30");
        });

        it('converts nested arrays', function () {
            $result = Util::arrayToString(['items' => [1, 2, 3]]);
            expect($result)->toContain('[1, 2, 3]');
        });

        it('handles null values', function () {
            $result = Util::arrayToString([null]);
            expect($result)->toContain('null');
        });

        it('handles boolean values', function () {
            $result = Util::arrayToString([true, false]);
            expect($result)->toContain('true')
                ->and($result)->toContain('false');
        });

        it('quotes string values', function () {
            $result = Util::arrayToString(['hello', 'world']);
            expect($result)->toContain("'hello'")
                ->and($result)->toContain("'world'");
        });

        it('preserves numeric keys for non-sequential arrays', function () {
            $result = Util::arrayToString([0 => 'a', 2 => 'b']);
            expect($result)->toContain("0 => 'a'")
                ->and($result)->toContain("2 => 'b'");
        });
    });

    describe('getDocBlocFactory', function () {
        it('returns a DocBlockFactory instance', function () {
            $factory = Util::getDocBlocFactory();
            expect($factory)->toBeInstanceOf(\phpDocumentor\Reflection\DocBlockFactoryInterface::class);
        });

        it('returns same instance on multiple calls (singleton)', function () {
            $factory1 = Util::getDocBlocFactory();
            $factory2 = Util::getDocBlocFactory();
            expect($factory1)->toBe($factory2);
        });
    });

    describe('getDocBlocContextFactory', function () {
        it('returns a ContextFactory instance', function () {
            $factory = Util::getDocBlocContextFactory();
            expect($factory)->toBeInstanceOf(\phpDocumentor\Reflection\Types\ContextFactory::class);
        });

        it('returns same instance on multiple calls (singleton)', function () {
            $factory1 = Util::getDocBlocContextFactory();
            $factory2 = Util::getDocBlocContextFactory();
            expect($factory1)->toBe($factory2);
        });
    });
});

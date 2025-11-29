<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, FunctionWrapper};
use JulienBoudry\PhpReference\Tests\Fixtures\ThrowsTagFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

// Ensure fixture functions are loaded
require_once __DIR__ . '/../../Fixtures/TestFunctions.php';

describe('@throws Tag Resolution', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('Method @throws tags', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ThrowsTagFixture::class));
        });

        it('resolves single @throws tag using getThrows', function (): void {
            $method = $this->classWrapper->methods['throwsOne'];
            $throwsTags = $method->getThrows();

            expect($throwsTags)->not->toBeNull()
                ->and($throwsTags)->toHaveCount(1);
        });

        it('resolves multiple @throws tags using getThrows', function (): void {
            $method = $this->classWrapper->methods['throwsMultiple'];
            $throwsTags = $method->getThrows();

            expect($throwsTags)->not->toBeNull()
                ->and($throwsTags)->toHaveCount(2);
        });

        it('returns null when no @throws tags', function (): void {
            $method = $this->classWrapper->methods['doesNotThrow'];
            $throwsTags = $method->getThrows();

            expect($throwsTags)->toBeNull();
        });

        it('resolves @throws tags with descriptions', function (): void {
            $method = $this->classWrapper->methods['throwsOne'];
            $throwsTags = $method->getThrows();

            $firstTag = reset($throwsTags);
            $description = $firstTag->getDescription()->render();

            expect($description)->toContain('argument is invalid');
        });

        it('resolves three @throws tags', function (): void {
            $method = $this->classWrapper->methods['throwsBasedOnType'];
            $throwsTags = $method->getThrows();

            expect($throwsTags)->toHaveCount(3);
        });

        it('gets resolved @throws tags with type information', function (): void {
            $method = $this->classWrapper->methods['throwsOne'];
            $resolved = $method->getResolvedThrowsTags();

            expect($resolved)->not->toBeNull()
                ->and($resolved)->toBeArray();
        });

        it('throwsOne resolves to InvalidArgumentException', function (): void {
            $method = $this->classWrapper->methods['throwsOne'];
            $throwsTags = $method->getThrows();

            $firstTag = reset($throwsTags);
            $type = (string) $firstTag->getType();

            expect($type)->toContain('InvalidArgumentException');
        });

        it('throwsLogicException resolves correctly', function (): void {
            $method = $this->classWrapper->methods['throwsLogicException'];
            $throwsTags = $method->getThrows();

            $firstTag = reset($throwsTags);
            $type = (string) $firstTag->getType();

            expect($type)->toContain('LogicException');
        });
    });

    describe('Function @throws tags', function (): void {
        it('function with @throws tag', function (): void {
            $functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionThatThrows')
            );

            $throwsTags = $functionWrapper->getThrows();

            expect($throwsTags)->not->toBeNull()
                ->and($throwsTags)->toHaveCount(1);
        });

        it('function @throws tag has correct type', function (): void {
            $functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionThatThrows')
            );

            $throwsTags = $functionWrapper->getThrows();
            $firstTag = reset($throwsTags);
            $type = (string) $firstTag->getType();

            expect($type)->toContain('InvalidArgumentException');
        });

        it('function without @throws returns null', function (): void {
            $functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction')
            );

            $throwsTags = $functionWrapper->getThrows();

            expect($throwsTags)->toBeNull();
        });
    });

    describe('getResolvedThrowsTags', function (): void {
        beforeEach(function (): void {
            $this->classWrapper = new ClassWrapper(new ReflectionClass(ThrowsTagFixture::class));
        });

        it('returns resolved throws tags array', function (): void {
            $method = $this->classWrapper->methods['throwsMultiple'];
            $resolved = $method->getResolvedThrowsTags();

            expect($resolved)->toBeArray()
                ->and($resolved)->toHaveCount(2);
        });

        it('resolved throws tags have destination and tag', function (): void {
            $method = $this->classWrapper->methods['throwsOne'];
            $resolved = $method->getResolvedThrowsTags();

            if (!empty($resolved)) {
                $firstResolved = reset($resolved);

                expect($firstResolved)->toHaveKey('destination')
                    ->and($firstResolved)->toHaveKey('tag');
            }
        });

        it('returns null for methods without throws', function (): void {
            $method = $this->classWrapper->methods['doesNotThrow'];
            $resolved = $method->getResolvedThrowsTags();

            expect($resolved)->toBeNull();
        });
    });
});

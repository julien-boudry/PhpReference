<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Writer\{ClassPageWriter, MethodPageWriter};
use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Tests\Fixtures\ExternalDependencyFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('External Dependencies', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        // Get the ClassWrapper from the code index so it has proper namespace initialization
        $this->classWrapper = $this->execution->codeIndex->getClassWrapper(ExternalDependencyFixture::class);
    });

    describe('Class extending external class', function (): void {
        it('can create a wrapper for class extending external class', function (): void {
            expect($this->classWrapper)->toBeInstanceOf(ClassWrapper::class);
        });

        it('can get the parent class name', function (): void {
            $parentClass = $this->classWrapper->reflection->getParentClass();

            expect($parentClass)->not->toBeFalse()
                ->and($parentClass->getName())->toBe('Exception');
        });

        it('generates signature with external parent class', function (): void {
            $signature = $this->classWrapper->getSignature();

            expect($signature)->toContain('extends Exception');
        });

        it('class page writer does not throw error', function (): void {
            $writer = new ClassPageWriter($this->classWrapper);
            $content = $writer->makeContent();

            expect($content)->toBeString()
                ->and($content)->toContain('ExternalDependencyFixture');
        });
    });

    describe('Methods with external parameter types', function (): void {
        it('can get method with external parameter type', function (): void {
            $method = $this->classWrapper->methods['processIterator'];

            expect($method)->not->toBeNull();
        });

        it('renders external parameter type as plain text (no link)', function (): void {
            $method = $this->classWrapper->methods['processIterator'];
            $parameters = $method->getParameters();
            // Get first parameter (iterator)
            $iteratorParam = $parameters[0];
            $urlLinker = $method->getUrlLinker();

            $typeMd = $iteratorParam->getTypeMd($urlLinker);

            // Should be rendered as code but NOT as a link
            expect($typeMd)->toContain('ArrayIterator')
                ->and($typeMd)->toContain('`')
                ->and($typeMd)->not->toContain('[`ArrayIterator`](');
        });

        it('method page writer does not throw error for external param types', function (): void {
            $method = $this->classWrapper->methods['processIterator'];
            $writer = new MethodPageWriter($method);
            $content = $writer->makeContent();

            expect($content)->toBeString()
                ->and($content)->toContain('ArrayIterator');
        });
    });

    describe('Methods with external return types', function (): void {
        it('can get method with external return type', function (): void {
            $method = $this->classWrapper->methods['createIterator'];

            expect($method)->not->toBeNull();
        });

        it('renders external return type as plain text (no link)', function (): void {
            $method = $this->classWrapper->methods['createIterator'];
            $urlLinker = $method->getUrlLinker();

            $typeMd = $method->getReturnTypeMd($urlLinker);

            // Should be rendered as code but NOT as a link
            expect($typeMd)->toContain('ArrayIterator')
                ->and($typeMd)->toContain('`')
                ->and($typeMd)->not->toContain('[`ArrayIterator`](');
        });

        it('renders Generator return type as plain text (no link)', function (): void {
            $method = $this->classWrapper->methods['getGenerator'];
            $urlLinker = $method->getUrlLinker();

            $typeMd = $method->getReturnTypeMd($urlLinker);

            expect($typeMd)->toContain('Generator')
                ->and($typeMd)->toContain('`')
                ->and($typeMd)->not->toContain('[`Generator`](');
        });

        it('method page writer does not throw error for external return types', function (): void {
            $method = $this->classWrapper->methods['createIterator'];
            $writer = new MethodPageWriter($method);
            $content = $writer->makeContent();

            expect($content)->toBeString()
                ->and($content)->toContain('ArrayIterator');
        });
    });

    describe('Methods with union types including external classes', function (): void {
        it('renders union type with external classes correctly', function (): void {
            $method = $this->classWrapper->methods['processTraversable'];
            $parameters = $method->getParameters();
            // Get first parameter (data)
            $dataParam = $parameters[0];
            $urlLinker = $method->getUrlLinker();

            $typeMd = $dataParam->getTypeMd($urlLinker);

            // Should contain both types as plain text
            expect($typeMd)->toContain('ArrayIterator')
                ->and($typeMd)->toContain('Traversable')
                ->and($typeMd)->toContain('|');
        });
    });

    describe('Methods with nullable external types', function (): void {
        it('renders nullable external type correctly', function (): void {
            $method = $this->classWrapper->methods['processNullableIterator'];
            $parameters = $method->getParameters();
            // Get first parameter (iterator)
            $iteratorParam = $parameters[0];
            $urlLinker = $method->getUrlLinker();

            $typeMd = $iteratorParam->getTypeMd($urlLinker);

            // Should be rendered as nullable code
            expect($typeMd)->toContain('ArrayIterator')
                ->and($typeMd)->toContain('?')
                ->and($typeMd)->not->toContain('[`');
        });
    });

    describe('@throws tags with external exceptions', function (): void {
        it('can get @throws tags for method throwing external exception', function (): void {
            $method = $this->classWrapper->methods['methodThatThrowsExternalException'];
            $throwsTags = $method->getThrows();

            expect($throwsTags)->not->toBeNull()
                ->and($throwsTags)->toHaveCount(1);
        });

        it('resolves @throws tag to null destination for external exception', function (): void {
            $method = $this->classWrapper->methods['methodThatThrowsExternalException'];
            $resolved = $method->getResolvedThrowsTags();

            expect($resolved)->not->toBeNull()
                ->and($resolved)->toHaveCount(1);

            $firstResolved = reset($resolved);
            // External exception should have null destination
            expect($firstResolved['destination'])->toBeNull();
        });

        it('resolves multiple external @throws tags', function (): void {
            $method = $this->classWrapper->methods['methodThatThrowsMultipleExternalExceptions'];
            $resolved = $method->getResolvedThrowsTags();

            expect($resolved)->not->toBeNull()
                ->and($resolved)->toHaveCount(3);

            // All should have null destination since they're all external
            foreach ($resolved as $item) {
                expect($item['destination'])->toBeNull();
            }
        });

        it('method page writer does not throw error for external @throws', function (): void {
            $method = $this->classWrapper->methods['methodThatThrowsExternalException'];
            $writer = new MethodPageWriter($method);
            $content = $writer->makeContent();

            expect($content)->toBeString()
                ->and($content)->toContain('RuntimeException')
                ->and($content)->toContain('Throws');
        });

        it('renders @throws with external exception as plain text (no link)', function (): void {
            $method = $this->classWrapper->methods['methodThatThrowsExternalException'];
            $writer = new MethodPageWriter($method);
            $content = $writer->makeContent();

            // Should contain the exception name as plain text (in backticks) but NOT as a markdown link
            // The exception name may include leading backslash like \RuntimeException
            expect($content)->toContain('RuntimeException')
                ->and($content)->toMatch('/`\\\\?RuntimeException`/')
                ->and($content)->not->toContain('[RuntimeException](')
                ->and($content)->not->toContain('[`RuntimeException`](')
                ->and($content)->not->toContain('[`\\RuntimeException`](');
        });
    });

    describe('Complete documentation generation', function (): void {
        it('generates full class page without errors', function (): void {
            $writer = new ClassPageWriter($this->classWrapper);
            $content = $writer->makeContent();

            expect($content)->toBeString()
                ->and($content)->not->toBeEmpty()
                ->and($content)->toContain('ExternalDependencyFixture')
                ->and($content)->toContain('extends Exception');
        });

        it('generates all method pages without errors', function (): void {
            foreach ($this->classWrapper->methods as $method) {
                if (!$method->isUserDefined()) {
                    continue;
                }

                $writer = new MethodPageWriter($method);
                $content = $writer->makeContent();

                expect($content)->toBeString("Method page generation failed for: {$method->name}")
                    ->and($content)->not->toBeEmpty("Method page is empty for: {$method->name}");
            }
        });
    });
});

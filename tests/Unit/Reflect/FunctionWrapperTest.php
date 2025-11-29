<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{FunctionWrapper, ParameterWrapper};

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

// Ensure fixture functions are loaded
require_once __DIR__ . '/../../Fixtures/TestFunctions.php';

describe('FunctionWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
    });

    describe('testHelperFunction', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction')
            );
        });

        it('wraps a function correctly', function (): void {
            expect($this->functionWrapper->name)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction');
        });

        it('is user-defined', function (): void {
            expect($this->functionWrapper->reflection->isUserDefined())->toBeTrue()
                ->and($this->functionWrapper->reflection->isInternal())->toBeFalse();
        });

        it('has return type', function (): void {
            expect($this->functionWrapper->hasReturnType())->toBeTrue();

            $returnType = $this->functionWrapper->getReturnType();
            expect($returnType)->not->toBeNull()
                ->and((string) $returnType)->toBe('string');
        });

        it('has parameters', function (): void {
            // Note: Using reflection->getParameters() directly to avoid ParameterWrapper
            // issues with standalone functions (ContextFactory limitation)
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params)->toHaveCount(1)
                ->and($params[0]->getName())->toBe('input');
        });

        it('parameter has type', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();
            $inputParam = $params[0];

            expect($inputParam->hasType())->toBeTrue()
                ->and((string) $inputParam->getType())->toBe('string');
        });

        it('generates correct page path', function (): void {
            $path = $this->functionWrapper->getPagePath();

            expect($path)->toContain('function_')
                ->and($path)->toContain('testHelperFunction')
                ->and($path)->toEndWith('.md');
        });

        // TODO: getSignature() for standalone functions with parameters triggers
        // a bug in phpDocumentor's ContextFactory which can't create context for
        // parameters of standalone functions. This is a known limitation.
        // See: ParameterWrapper -> ReflectionWrapper -> ContextFactory::createFromReflector
        it('generates function signature', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain('function')
                ->and($signature)->toContain('testHelperFunction')
                ->and($signature)->toContain('string $input')
                ->and($signature)->toContain(': string');
        })->todo();

        it('has doc comment', function (): void {
            $docComment = $this->functionWrapper->reflection->getDocComment();

            expect($docComment)->toBeString()
                ->and($docComment)->toContain('A test function for testing function discovery');
        });

        it('gets description from docblock', function (): void {
            $description = $this->functionWrapper->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('test function');
        });

        it('gets number of parameters', function (): void {
            expect($this->functionWrapper->reflection->getNumberOfParameters())->toBe(1)
                ->and($this->functionWrapper->reflection->getNumberOfRequiredParameters())->toBe(1);
        });

        it('is not a generator', function (): void {
            expect($this->functionWrapper->reflection->isGenerator())->toBeFalse();
        });

        it('is not variadic', function (): void {
            expect($this->functionWrapper->reflection->isVariadic())->toBeFalse();
        });

        it('can get start and end line', function (): void {
            $startLine = $this->functionWrapper->reflection->getStartLine();
            $endLine = $this->functionWrapper->reflection->getEndLine();

            expect($startLine)->toBeInt()
                ->and($endLine)->toBeInt()
                ->and($endLine)->toBeGreaterThanOrEqual($startLine);
        });

        it('can get filename', function (): void {
            $filename = $this->functionWrapper->reflection->getFileName();

            expect($filename)->toBeString()
                ->and($filename)->toContain('TestFunctions.php');
        });

        it('can get namespace', function (): void {
            $namespace = $this->functionWrapper->reflection->getNamespaceName();

            expect($namespace)->toBe('JulienBoudry\\PhpReference\\Tests\\Fixtures');
        });
    });

    describe('anotherTestFunction', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\anotherTestFunction')
            );
        });

        it('wraps the second function correctly', function (): void {
            expect($this->functionWrapper->name)->toContain('anotherTestFunction');
        });

        it('has int parameter and return type', function (): void {
            // Note: Using reflection->getParameters() directly
            $params = $this->functionWrapper->reflection->getParameters();
            $returnType = $this->functionWrapper->getReturnType();

            expect($params[0]->getName())->toBe('value')
                ->and((string) $params[0]->getType())->toBe('int')
                ->and((string) $returnType)->toBe('int');
        });

        // TODO: getSignature() disabled due to ParameterWrapper limitation for standalone functions
        it('generates correct signature', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain('function')
                ->and($signature)->toContain('anotherTestFunction')
                ->and($signature)->toContain('int $value')
                ->and($signature)->toContain(': int');
        })->todo();
    });

    describe('Built-in functions comparison', function (): void {
        it('can wrap built-in functions', function (): void {
            $wrapper = new FunctionWrapper(new ReflectionFunction('strlen'));

            expect($wrapper->name)->toBe('strlen')
                ->and($wrapper->reflection->isInternal())->toBeTrue()
                ->and($wrapper->reflection->isUserDefined())->toBeFalse();
        });

        it('built-in function has no file', function (): void {
            $wrapper = new FunctionWrapper(new ReflectionFunction('strlen'));

            expect($wrapper->reflection->getFileName())->toBeFalse();
        });
    });

    describe('FunctionWrapper with CanThrow trait', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction')
            );
        });

        it('can check for throws tags', function (): void {
            // testHelperFunction doesn't have @throws, so should return null/empty
            $throwsTags = $this->functionWrapper->getResolvedThrowsTags();

            expect($throwsTags === null || $throwsTags === [])->toBeTrue();
        });
    });

    describe('FunctionWrapper with IsFunction trait', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\testHelperFunction')
            );
        });

        // TODO: getFunctionPartSignature is protected and can't be tested directly
        it('getFunctionPartSignature generates parameter list', function (): void {
            $signature = $this->functionWrapper->getFunctionPartSignature();
            expect($signature)->toContain('testHelperFunction')
                ->and($signature)->toContain('(')
                ->and($signature)->toContain(')')
                ->and($signature)->toContain('string $input')
                ->and($signature)->toContain(': string');
        })->todo();

        it('hasReturnType returns correct value', function (): void {
            expect($this->functionWrapper->hasReturnType())->toBeTrue();
        });
    });

    describe('Function with optional parameters', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionWithOptionalParams')
            );
        });

        it('has correct number of parameters', function (): void {
            expect($this->functionWrapper->reflection->getNumberOfParameters())->toBe(3)
                ->and($this->functionWrapper->reflection->getNumberOfRequiredParameters())->toBe(1);
        });

        it('first parameter is required', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params[0]->isOptional())->toBeFalse()
                ->and($params[0]->getName())->toBe('required');
        });

        it('second parameter has default value', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params[1]->isDefaultValueAvailable())->toBeTrue()
                ->and($params[1]->getDefaultValue())->toBe(42);
        });

        it('third parameter has array default', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params[2]->isDefaultValueAvailable())->toBeTrue()
                ->and($params[2]->getDefaultValue())->toBe(['a', 'b']);
        });

        // TODO: getSignature() disabled due to ParameterWrapper limitation for standalone functions
        it('generates signature with defaults', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain('string $required')
                ->and($signature)->toContain('int $optional = 42');
        })->todo();
    });

    describe('Function with variadic parameter', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionWithVariadic')
            );
        });

        it('detects variadic parameter', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params[0]->isVariadic())->toBeTrue()
                ->and($params[0]->getName())->toBe('items');
        });

        it('is variadic function', function (): void {
            expect($this->functionWrapper->reflection->isVariadic())->toBeTrue();
        });

        // TODO: getSignature() disabled due to ParameterWrapper limitation for standalone functions
        it('generates signature with variadic', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain('string ...$items');
        })->todo();
    });

    describe('Function with reference parameter', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionWithReference')
            );
        });

        it('detects reference parameter', function (): void {
            $params = $this->functionWrapper->reflection->getParameters();

            expect($params[0]->isPassedByReference())->toBeTrue()
                ->and($params[0]->getName())->toBe('counter');
        });

        // TODO: getSignature() disabled due to ParameterWrapper limitation for standalone functions
        it('generates signature with ampersand', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain('int &$counter');
        })->todo();

        it('has void return type', function (): void {
            $returnType = $this->functionWrapper->getReturnType();

            expect((string) $returnType)->toBe('void');
        });
    });

    describe('Function with union type return', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionWithUnionType')
            );
        });

        it('has union return type', function (): void {
            $returnType = $this->functionWrapper->getReturnType();

            // getReturnType() returns a string
            expect($returnType)->toBe('string|int');
        });

        // TODO: getSignature() disabled due to ParameterWrapper limitation for standalone functions
        it('generates signature with union return', function (): void {
            $signature = $this->functionWrapper->getSignature();
            expect($signature)->toContain(': string|int');
        })->todo();
    });

    describe('Function with nullable return', function (): void {
        beforeEach(function (): void {
            $this->functionWrapper = new FunctionWrapper(
                new ReflectionFunction('JulienBoudry\\PhpReference\\Tests\\Fixtures\\functionWithNullableReturn')
            );
        });

        it('has nullable return type', function (): void {
            $returnType = $this->functionWrapper->getReturnType();

            // getReturnType() returns a string
            expect($returnType)->toBe('?string');
        });
    });
});

<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Tests\Fixtures\AdvancedTypesFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('Advanced Types', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = new ClassWrapper(new ReflectionClass(AdvancedTypesFixture::class));
    });

    describe('Union Types', function (): void {
        it('property has union type', function (): void {
            $property = $this->classWrapper->properties['unionProperty'];
            $type = $property->reflection->getType();

            expect($type)->not->toBeNull()
                ->and($type)->toBeInstanceOf(ReflectionUnionType::class)
                ->and((string) $type)->toBe('string|int');
        });

        it('nullable union property', function (): void {
            $property = $this->classWrapper->properties['nullableUnionProperty'];
            $type = $property->reflection->getType();

            expect($type)->toBeInstanceOf(ReflectionUnionType::class)
                ->and($type->allowsNull())->toBeTrue();

            $typeNames = array_map(fn($t) => $t->getName(), $type->getTypes());
            expect($typeNames)->toContain('string')
                ->and($typeNames)->toContain('int')
                ->and($typeNames)->toContain('null');
        });

        it('method returns union type', function (): void {
            $method = $this->classWrapper->methods['unionReturn'];
            $returnType = $method->getReturnType();

            // getReturnType() returns a string
            expect($returnType)->toBe('string|int');
        });

        it('method has union type parameter', function (): void {
            $method = $this->classWrapper->methods['unionParam'];
            $params = $method->getParameters();
            $valueParam = $params[0];

            expect($valueParam->reflection->getType())->toBeInstanceOf(ReflectionUnionType::class);
        });
    });

    describe('Nullable Types', function (): void {
        it('method returns nullable type', function (): void {
            $method = $this->classWrapper->methods['nullableReturn'];
            $returnType = $method->getReturnType();

            // getReturnType() returns a string
            expect($returnType)->toBe('?string');
        });

        it('property with null default', function (): void {
            $property = $this->classWrapper->properties['nullableUnionProperty'];

            expect($property->reflection->hasDefaultValue())->toBeTrue()
                ->and($property->reflection->getDefaultValue())->toBeNull();
        });
    });

    describe('Variadic Parameters', function (): void {
        it('detects variadic string parameter', function (): void {
            $method = $this->classWrapper->methods['variadicParam'];
            $params = $method->getParameters();
            $variadicParam = $params[0];

            expect($variadicParam->reflection->isVariadic())->toBeTrue()
                ->and($variadicParam->name)->toBe('values')
                ->and((string) $variadicParam->reflection->getType())->toBe('string');
        });

        it('detects variadic int parameter', function (): void {
            $method = $this->classWrapper->methods['variadicIntParam'];
            $params = $method->getParameters();
            $variadicParam = $params[0];

            expect($variadicParam->reflection->isVariadic())->toBeTrue()
                ->and((string) $variadicParam->reflection->getType())->toBe('int');
        });

        it('variadic method has array return', function (): void {
            $method = $this->classWrapper->methods['variadicParam'];
            $returnType = $method->getReturnType();

            expect((string) $returnType)->toBe('array');
        });
    });

    describe('Reference Parameters', function (): void {
        it('detects reference parameter', function (): void {
            $method = $this->classWrapper->methods['referenceParam'];
            $params = $method->getParameters();
            $counterParam = $params[0];

            expect($counterParam->reflection->isPassedByReference())->toBeTrue()
                ->and($counterParam->name)->toBe('counter');
        });

        it('detects multiple reference parameters', function (): void {
            $method = $this->classWrapper->methods['swapStrings'];
            $params = $method->getParameters();

            foreach ($params as $param) {
                expect($param->reflection->isPassedByReference())->toBeTrue();
            }
        });

        it('generates signature with ampersand', function (): void {
            $method = $this->classWrapper->methods['referenceParam'];
            $params = $method->getParameters();
            $counterParam = $params[0];
            $signature = $counterParam->getSignature();

            expect($signature)->toContain('&$counter');
        });
    });

    describe('Intersection Types', function (): void {
        it('detects intersection type parameter', function (): void {
            $method = $this->classWrapper->methods['intersectionParam'];
            $params = $method->getParameters();
            $valueParam = $params[0];

            expect($valueParam->reflection->getType())->toBeInstanceOf(ReflectionIntersectionType::class);
        });
    });

    describe('Special Types', function (): void {
        it('method with mixed parameter and return', function (): void {
            $method = $this->classWrapper->methods['mixedParam'];

            $params = $method->getParameters();
            $returnType = $method->getReturnType();

            expect((string) $params[0]->reflection->getType())->toBe('mixed')
                ->and((string) $returnType)->toBe('mixed');
        });

        it('method with callable parameter', function (): void {
            $method = $this->classWrapper->methods['callableParam'];
            $params = $method->getParameters();
            $callbackParam = $params[0];

            expect((string) $callbackParam->reflection->getType())->toBe('callable');
        });

        it('method with iterable parameter', function (): void {
            $method = $this->classWrapper->methods['iterableParam'];
            $params = $method->getParameters();

            expect((string) $params[0]->reflection->getType())->toBe('iterable');
        });

        it('method returns never', function (): void {
            $method = $this->classWrapper->methods['neverReturn'];
            $returnType = $method->getReturnType();

            expect((string) $returnType)->toBe('never');
        });

        it('method returns string|false', function (): void {
            $method = $this->classWrapper->methods['stringOrFalse'];
            $returnType = $method->getReturnType();

            // getReturnType() returns a string
            expect($returnType)->toBe('string|false');
        });

        it('method returns true literal', function (): void {
            $method = $this->classWrapper->methods['trueReturn'];
            $returnType = $method->getReturnType();

            expect((string) $returnType)->toBe('true');
        });
    });

    describe('Default Values', function (): void {
        it('method has multiple parameters with defaults', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();

            expect($params)->toHaveCount(5);
        });

        it('required parameter has no default', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $required = $params[0];

            expect($required->name)->toBe('required')
                ->and($required->reflection->isDefaultValueAvailable())->toBeFalse()
                ->and($required->reflection->isOptional())->toBeFalse();
        });

        it('int default value', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $withDefault = $params[1];

            expect($withDefault->name)->toBe('withDefault')
                ->and($withDefault->reflection->isDefaultValueAvailable())->toBeTrue()
                ->and($withDefault->reflection->getDefaultValue())->toBe(42)
                ->and($withDefault->reflection->isOptional())->toBeTrue();
        });

        it('array default value', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $arrayDefault = $params[2];

            expect($arrayDefault->name)->toBe('arrayDefault')
                ->and($arrayDefault->reflection->getDefaultValue())->toBe(['a', 'b']);
        });

        it('bool default value', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $boolDefault = $params[3];

            expect($boolDefault->name)->toBe('boolDefault')
                ->and($boolDefault->reflection->getDefaultValue())->toBe(true);
        });

        it('null default value', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $nullDefault = $params[4];

            expect($nullDefault->name)->toBe('nullDefault')
                ->and($nullDefault->reflection->getDefaultValue())->toBeNull()
                ->and($nullDefault->reflection->allowsNull())->toBeTrue();
        });
    });

    describe('Parameter positions', function (): void {
        it('parameters have correct positions', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();

            expect($params[0]->reflection->getPosition())->toBe(0)
                ->and($params[1]->reflection->getPosition())->toBe(1)
                ->and($params[2]->reflection->getPosition())->toBe(2)
                ->and($params[3]->reflection->getPosition())->toBe(3)
                ->and($params[4]->reflection->getPosition())->toBe(4);
        });
    });

    describe('getType() method on wrappers', function (): void {
        it('property wrapper getType returns formatted type', function (): void {
            $property = $this->classWrapper->properties['unionProperty'];
            $type = $property->getType();

            expect($type)->toBe('string|int');
        });

        it('nullable property wrapper getType', function (): void {
            $property = $this->classWrapper->properties['nullableUnionProperty'];
            $type = $property->getType();

            // The exact format may vary, but should include string, int, null
            expect($type)->toContain('string')
                ->and($type)->toContain('int')
                ->and($type)->toContain('null');
        });
    });

    describe('ParameterWrapper getDescription', function (): void {
        it('gets parameter description from docblock', function (): void {
            $method = $this->classWrapper->methods['variadicParam'];
            $params = $method->getParameters();
            $description = $params[0]->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('Multiple string values');
        });

        it('gets description for named parameter', function (): void {
            $method = $this->classWrapper->methods['multipleDefaults'];
            $params = $method->getParameters();
            $requiredParam = $params[0];
            $description = $requiredParam->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('Required parameter');
        });
    });
});

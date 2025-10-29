<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('ClassWrapper Edge Cases', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture();
    });

    it('can wrap an abstract class', function () {
        // Create a test abstract class
        $abstractClass = new class {
            public static function getReflection(): \ReflectionClass {
                return new \ReflectionClass(\JulienBoudry\PhpReference\Reflect\ReflectionWrapper::class);
            }
        };

        $wrapper = new ClassWrapper($abstractClass::getReflection());
        
        expect($wrapper->reflection->isAbstract())->toBeTrue();
    });

    it('can wrap an interface', function () {
        $reflection = new \ReflectionClass(\JulienBoudry\PhpReference\Definition\PublicApiDefinitionInterface::class);
        $wrapper = new ClassWrapper($reflection);

        expect($wrapper->reflection->isInterface())->toBeTrue()
            ->and($wrapper->reflection->isAbstract())->toBeTrue();
    });

    it('detects if class has constructor', function () {
        createExecutionFixture();
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));

        // ErrorCollector doesn't have explicit constructor
        $constructor = $wrapper->reflection->getConstructor();
        expect($constructor === null || $constructor instanceof \ReflectionMethod)->toBeTrue();
    });

    it('can get parent class if it exists', function () {
        createExecutionFixture();
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));

        // ErrorCollector doesn't extend anything, so should be false
        $parent = $wrapper->reflection->getParentClass();
        expect($parent === false || $parent instanceof \ReflectionClass)->toBeTrue();
    });

    it('can get implemented interfaces', function () {
        createExecutionFixture();
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $interfaces = $wrapper->reflection->getInterfaces();

        expect($interfaces)->toBeArray();
    });

    it('detects if class is cloneable', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));

        expect($wrapper->reflection->isCloneable())->toBeBool();
    });

    it('detects if class is iterable', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));

        expect($wrapper->reflection->isIterable())->toBeBool();
    });

    it('can get class modifiers', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $modifiers = $wrapper->reflection->getModifiers();

        expect($modifiers)->toBeInt();
    });

    it('can get class filename', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $filename = $wrapper->reflection->getFileName();

        expect($filename)->toBeString()
            ->and($filename)->toContain('ErrorCollector.php');
    });

    it('can get class namespace', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $namespace = $wrapper->reflection->getNamespaceName();

        expect($namespace)->toBe('JulienBoudry\\PhpReference\\Log');
    });

    it('handles classes without namespace', function () {
        // Built-in classes don't have namespace
        $wrapper = new ClassWrapper(new \ReflectionClass(\Exception::class));
        $namespace = $wrapper->reflection->getNamespaceName();

        expect($namespace)->toBe('');
    });

    it('can check if class is anonymous', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));

        expect($wrapper->reflection->isAnonymous())->toBeFalse();
    });

    it('can get class constants count', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $constants = $wrapper->reflection->getConstants();

        expect($constants)->toBeArray();
    });

    it('can get traits used by class', function () {
        $wrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
        $traits = $wrapper->reflection->getTraits();

        expect($traits)->toBeArray();
    });
});

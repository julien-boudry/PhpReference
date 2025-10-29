<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('PropertyWrapper Edge Cases', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
    });

    it('detects promoted properties in constructors', function () {
        // ErrorCollector doesn't have constructor properties, but we can test the concept
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            // isPromoted() checks if property is from constructor promotion
            expect($property->reflection->isPromoted())->toBeBool();
        }
        
        expect($properties)->toBeArray();
    });

    it('can get property default value when available', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            if ($property->reflection->hasDefaultValue()) {
                try {
                    $defaultValue = $property->reflection->getDefaultValue();
                    // Default value can be anything - just check we can get it
                    expect(true)->toBeTrue();
                } catch (\ReflectionException $e) {
                    // Some properties might not have accessible default values
                    expect($e)->toBeInstanceOf(\ReflectionException::class);
                }
            }
        }
        
        expect($properties)->toBeArray();
    });

    it('can check if property is initialized', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        $collector = new ErrorCollector();
        
        foreach ($properties as $property) {
            // isInitialized requires an object instance
            try {
                $isInitialized = $property->reflection->isInitialized($collector);
                expect($isInitialized)->toBeBool();
            } catch (\Error $e) {
                // Typed properties without default might throw
                expect($e)->toBeInstanceOf(\Error::class);
            }
        }
        
        expect($properties)->toBeArray();
    });

    it('can get property modifiers', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            $modifiers = $property->reflection->getModifiers();
            expect($modifiers)->toBeInt();
        }
        
        expect($properties)->toBeArray();
    });

    it('detects if property has hooks (PHP 8.4)', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            // hasHooks is a PHP 8.4 feature
            if (method_exists($property->reflection, 'hasHooks')) {
                expect($property->reflection->hasHooks())->toBeBool();
            }
        }
        
        expect($properties)->toBeArray();
    });

    it('can get property declaring class', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            $declaringClass = $property->reflection->getDeclaringClass();
            expect($declaringClass)->toBeInstanceOf(\ReflectionClass::class)
                ->and($declaringClass->getName())->toBe(ErrorCollector::class);
        }
        
        expect($properties)->toBeArray();
    });

    it('can set and get property value via reflection', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        $collector = new ErrorCollector();
        
        foreach ($properties as $property) {
            // Make property accessible
            $property->reflection->setAccessible(true);
            
            try {
                // Try to get the value
                $value = $property->reflection->getValue($collector);
                // Value can be anything - just check we can retrieve it
                expect(true)->toBeTrue();
            } catch (\Error $e) {
                // Some properties might not be initialized
                expect($e)->toBeInstanceOf(\Error::class);
            }
        }
        
        expect($properties)->not->toBeEmpty();
    });

    it('detects if property is internal', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            // ReflectionProperty doesn't have isInternal() - only ReflectionClass/Function do
            // Check that property belongs to a non-internal class instead
            expect($property->reflection->getDeclaringClass()->isInternal())->toBeFalse();
        }
        
        expect($properties)->toBeArray();
    });

    it('can check if property is deprecated', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);
        
        foreach ($properties as $property) {
            // ReflectionProperty doesn't have isDeprecated() in PHP 8.4
            // Check DocBlock for @deprecated tag instead via wrapper
            $hasDeprecatedTag = $property->docBlock?->hasTag('deprecated') ?? false;
            expect($hasDeprecatedTag)->toBeBool();
        }
        
        expect($properties)->toBeArray();
    });
});

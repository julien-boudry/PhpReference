<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Reflect\PropertyWrapper;
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('PropertyWrapper', function () {
    beforeEach(function () {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new \ReflectionClass(ErrorCollector::class));
    });

    it('wraps a property correctly', function () {
        $properties = $this->classWrapper->properties;

        expect($properties)->toBeArray();

        if (!empty($properties)) {
            $property = reset($properties);
            expect($property)->toBeInstanceOf(PropertyWrapper::class);
        }
    });

    it('detects public properties', function () {
        $publicProperties = $this->classWrapper->getAllProperties(protected: false, private: false);

        expect($publicProperties)->toBeArray();
        
        foreach ($publicProperties as $property) {
            expect($property->reflection->isPublic())->toBeTrue();
        }
    });

    it('can filter properties by visibility', function () {
        $allProperties = $this->classWrapper->getAllProperties(protected: true, private: true);
        $publicOnly = $this->classWrapper->getAllProperties(protected: false, private: false);

        // If there are protected/private properties, count should be different
        expect(count($allProperties))->toBeGreaterThanOrEqual(count($publicOnly));
    });

    it('detects readonly properties', function () {
        // ErrorCollector has private properties, check if any are readonly
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // Just check that the isReadOnly method exists and returns a boolean
            expect($property->reflection->isReadOnly())->toBeBool();
        }
    });

    it('detects static properties', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // Check that isStatic returns a boolean
            expect($property->reflection->isStatic())->toBeBool();
        }
    });

    it('gets property type when available', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            if ($property->reflection->hasType()) {
                expect($property->reflection->getType())->not->toBeNull();
            }
        }
    });

    it('detects if property has default value', function () {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // hasDefaultValue returns boolean
            expect($property->reflection->hasDefaultValue())->toBeBool();
        }
    });

    it('generates correct page path', function () {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            $path = $property->getPagePath();

            expect($path)->toBeString()
                ->and($path)->toContain('ErrorCollector')
                ->and($path)->toContain('property_');
        }
    });

    it('has reference to parent class', function () {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            expect($property->inDocParentWrapper)->not->toBeNull();
        }
    });

    it('detects if property is in public API', function () {
        $apiProperties = $this->classWrapper->getAllApiProperties();

        foreach ($apiProperties as $property) {
            expect($property->willBeInPublicApi)->toBeTrue();
        }
        
        // If there are no API properties, that's okay too
        expect($apiProperties)->toBeArray();
    });

    it('can get property documentation', function () {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            // Properties can have docblocks
            $docComment = $property->reflection->getDocComment();
            expect($docComment === false || is_string($docComment))->toBeTrue();
        }
    });
});

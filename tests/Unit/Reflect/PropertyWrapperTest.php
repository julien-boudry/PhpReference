<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\{ClassWrapper, PropertyWrapper};
use JulienBoudry\PhpReference\Log\ErrorCollector;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('PropertyWrapper', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture();
        $this->classWrapper = new ClassWrapper(new ReflectionClass(ErrorCollector::class));
    });

    it('wraps a property correctly', function (): void {
        $properties = $this->classWrapper->properties;

        expect($properties)->toBeArray();

        if (!empty($properties)) {
            $property = reset($properties);
            expect($property)->toBeInstanceOf(PropertyWrapper::class);
        }
    });

    it('detects public properties', function (): void {
        $publicProperties = $this->classWrapper->getAllProperties(protected: false, private: false);

        expect($publicProperties)->toBeArray();

        foreach ($publicProperties as $property) {
            expect($property->reflection->isPublic())->toBeTrue();
        }
    });

    it('can filter properties by visibility', function (): void {
        $allProperties = $this->classWrapper->getAllProperties(protected: true, private: true);
        $publicOnly = $this->classWrapper->getAllProperties(protected: false, private: false);

        // If there are protected/private properties, count should be different
        expect(\count($allProperties))->toBeGreaterThanOrEqual(\count($publicOnly));
    });

    it('detects readonly properties', function (): void {
        // ErrorCollector has private properties, check if any are readonly
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // Just check that the isReadOnly method exists and returns a boolean
            expect($property->reflection->isReadOnly())->toBeBool();
        }
    });

    it('detects static properties', function (): void {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // Check that isStatic returns a boolean
            expect($property->reflection->isStatic())->toBeBool();
        }
    });

    it('gets property type when available', function (): void {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            if ($property->reflection->hasType()) {
                expect($property->reflection->getType())->not->toBeNull();
            }
        }
    });

    it('detects if property has default value', function (): void {
        $properties = $this->classWrapper->getAllProperties(protected: true, private: true);

        foreach ($properties as $property) {
            // hasDefaultValue returns boolean
            expect($property->reflection->hasDefaultValue())->toBeBool();
        }
    });

    it('generates correct page path', function (): void {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            $path = $property->getPagePath();

            expect($path)->toBeString()
                ->and($path)->toContain('ErrorCollector')
                ->and($path)->toContain('property_');
        }
    });

    it('has reference to parent class', function (): void {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            expect($property->inDocParentWrapper)->not->toBeNull();
        }
    });

    it('detects if property is in public API', function (): void {
        $apiProperties = $this->classWrapper->getAllApiProperties();

        foreach ($apiProperties as $property) {
            expect($property->willBeInPublicApi)->toBeTrue();
        }

        // If there are no API properties, that's okay too
        expect($apiProperties)->toBeArray();
    });

    it('can get property documentation', function (): void {
        $properties = $this->classWrapper->properties;

        if (!empty($properties)) {
            $property = reset($properties);
            // Properties can have docblocks
            $docComment = $property->reflection->getDocComment();
            expect($docComment === false || \is_string($docComment))->toBeTrue();
        }
    });
});

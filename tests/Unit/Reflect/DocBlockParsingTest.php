<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Reflect\ClassWrapper;
use JulienBoudry\PhpReference\Tests\Fixtures\DocBlockFixture;

use function JulienBoudry\PhpReference\Tests\createExecutionFixture;

describe('DocBlock parsing', function (): void {
    beforeEach(function (): void {
        $this->execution = createExecutionFixture(
            namespace: 'JulienBoudry\\PhpReference\\Tests\\Fixtures'
        );
        $this->classWrapper = new ClassWrapper(new ReflectionClass(DocBlockFixture::class));
    });

    describe('getSummary', function (): void {
        it('returns only the first paragraph for class', function (): void {
            $summary = $this->classWrapper->getSummary();

            expect($summary)->not->toBeNull()
                ->and($summary)->toContain('summary line for the class')
                ->and($summary)->not->toContain('first paragraph of the description');
        });

        it('returns summary for property with summary only', function (): void {
            $property = $this->classWrapper->properties['summaryOnly'];
            $summary = $property->getSummary();

            expect($summary)->not->toBeNull()
                ->and($summary)->toContain('Summary only');
        });

        it('returns only summary for property with summary and description', function (): void {
            $property = $this->classWrapper->properties['summaryAndDescription'];
            $summary = $property->getSummary();

            expect($summary)->not->toBeNull()
                ->and($summary)->toContain('Summary line for the property')
                ->and($summary)->not->toContain('Description paragraph');
        });

        it('returns null when only tags are present', function (): void {
            $method = $this->classWrapper->methods['methodWithOnlyTags'];
            $summary = $method->getSummary();

            expect($summary)->toBeNull();
        });
    });

    describe('getDescription', function (): void {
        it('parses class description with summary and multiple paragraphs', function (): void {
            $description = $this->classWrapper->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('summary line for the class')
                ->and($description)->toContain('first paragraph of the description')
                ->and($description)->toContain('second paragraph of the description');
        });

        it('parses property with summary only', function (): void {
            $property = $this->classWrapper->properties['summaryOnly'];
            $description = $property->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('Summary only');
        });

        it('parses property with summary and description', function (): void {
            $property = $this->classWrapper->properties['summaryAndDescription'];
            $description = $property->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('Summary line for the property')
                ->and($description)->toContain('Description paragraph for the property');
        });

        it('parses method with multiple paragraphs', function (): void {
            $method = $this->classWrapper->methods['methodWithMultipleParagraphs'];
            $description = $method->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('First line summary')
                ->and($description)->toContain('Second paragraph is the description')
                ->and($description)->toContain('Third paragraph continues the description');
        });

        it('parses method with params and returns summary + description', function (): void {
            $method = $this->classWrapper->methods['methodWithParams'];
            $description = $method->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('Summary for method with params')
                ->and($description)->toContain('Description explains what this method does');
        });

        it('returns null or empty description when only tags are present', function (): void {
            $method = $this->classWrapper->methods['methodWithOnlyTags'];
            $description = $method->getDescription();

            // Should return null or empty string when there's no summary/description
            expect($description === null || $description === '')->toBeTrue();
        });

        it('preserves markdown formatting in description', function (): void {
            $method = $this->classWrapper->methods['methodWithMarkdown'];
            $description = $method->getDescription();

            expect($description)->not->toBeNull()
                ->and($description)->toContain('`code`')
                ->and($description)->toContain('*italic*')
                ->and($description)->toContain('**bold**')
                ->and($description)->toContain('- List item 1')
                ->and($description)->toContain('`inline code`');
        });
    });

    describe('getShortDescriptionForTable', function (): void {
        it('uses summary only for table display', function (): void {
            $method = $this->classWrapper->methods['methodWithMultipleParagraphs'];
            $shortDesc = $method->getShortDescriptionForTable();

            expect($shortDesc)->not->toBeNull()
                ->and($shortDesc)->toContain('First line summary')
                ->and($shortDesc)->not->toContain('Second paragraph');
        });

        it('removes line breaks for table display', function (): void {
            $shortDesc = $this->classWrapper->getShortDescriptionForTable();

            expect($shortDesc)->not->toBeNull()
                ->and($shortDesc)->not->toContain("\n");
        });

        it('removes markdown table-breaking characters', function (): void {
            $method = $this->classWrapper->methods['methodWithMarkdown'];
            $shortDesc = $method->getShortDescriptionForTable();

            expect($shortDesc)->not->toBeNull()
                ->and($shortDesc)->not->toContain('|')
                ->and($shortDesc)->not->toContain('`');
        });
    });
});

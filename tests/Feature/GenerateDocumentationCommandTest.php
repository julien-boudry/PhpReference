<?php

declare(strict_types=1);

use JulienBoudry\PhpReference\Command\GenerateDocumentationCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

describe('GenerateDocumentationCommand', function (): void {
    beforeEach(function (): void {
        $this->application = new Application;
        $this->command = new GenerateDocumentationCommand;
        $this->application->addCommand($this->command);
        $this->commandTester = new CommandTester($this->command);
    });

    it('can be created', function (): void {
        expect($this->command)->toBeInstanceOf(GenerateDocumentationCommand::class);
    });

    it('has correct name', function (): void {
        expect($this->command->getName())->toBe('generate:documentation');
    });

    it('has description', function (): void {
        expect($this->command->getDescription())->not->toBeEmpty();
    });

    it('defines namespace argument', function (): void {
        $definition = $this->command->getDefinition();
        expect($definition->hasArgument('namespace'))->toBeTrue();
    });

    it('defines output option', function (): void {
        $definition = $this->command->getDefinition();
        expect($definition->hasOption('output'))->toBeTrue();
    });

    it('defines api option', function (): void {
        $definition = $this->command->getDefinition();
        expect($definition->hasOption('api'))->toBeTrue();
    });

    it('defines append option', function (): void {
        $definition = $this->command->getDefinition();
        expect($definition->hasOption('append'))->toBeTrue();
    });

    it('defines config option', function (): void {
        $definition = $this->command->getDefinition();
        expect($definition->hasOption('config'))->toBeTrue();
    });
});

<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Command\GenerateDocumentationCommand;
use Symfony\Component\Console\Application;

abstract class App
{
    public const string VERSION = '1.0.0';

    public const string NAME = 'PHP Reference Documentation Generator';

    public static function getFullName(): string
    {
        return \sprintf('%s v%s', self::NAME, self::VERSION);
    }

    public static function runApplication(): void
    {
        $application = new Application(
            name : self::NAME,
            version: self::VERSION,
        );

        $generateCommand = new GenerateDocumentationCommand;

        $application->add($generateCommand);
        $application->setDefaultCommand($generateCommand->getName(), true);

        $application->run();
    }
}

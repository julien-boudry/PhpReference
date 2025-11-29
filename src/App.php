<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference;

use JulienBoudry\PhpReference\Command\GenerateDocumentationCommand;
use Symfony\Component\Console\Application;

/**
 * Main application entry point for the PHP Reference Documentation Generator.
 *
 * This abstract class provides the core application configuration and bootstrap
 * functionality. It defines application metadata (name, version) and initializes
 * the Symfony Console application with the documentation generation command.
 *
 * @see GenerateDocumentationCommand The main command for generating documentation
 */
abstract class App
{
    /**
     * Current version of the application.
     */
    public const string VERSION = '1.0.0';

    /**
     * Human-readable name of the application.
     */
    public const string NAME = 'PHP Reference Documentation Generator';

    /**
     * Returns the full application name including version number.
     *
     * Combines the application name and version into a formatted string
     * suitable for display in headers and titles.
     */
    public static function getFullName(): string
    {
        return \sprintf('%s v%s', self::NAME, self::VERSION);
    }

    /**
     * Creates and runs the Symfony Console application.
     *
     * This method bootstraps the entire CLI application by:
     * 1. Creating a new Symfony Console Application instance
     * 2. Registering the documentation generation command
     * 3. Setting it as the default command (runs when no command is specified)
     * 4. Executing the application
     */
    public static function runApplication(): void
    {
        $application = new Application(
            name : self::NAME,
            version: self::VERSION,
        );

        $generateCommand = new GenerateDocumentationCommand;

        $application->addCommand($generateCommand);
        $application->setDefaultCommand($generateCommand->getName(), true);

        $application->run();
    }
}

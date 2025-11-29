<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Command;

use JulienBoudry\PhpReference\Writer\AbstractWriter;
use SebastianBergmann\Timer\{ResourceUsageFormatter, Timer};
use JulienBoudry\PhpReference\{App, CodeIndex, Config, Execution};
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Laravel\Prompts\{confirm, error, info, note, progress, warning};

/**
 * Console command that generates API documentation for a PHP namespace.
 *
 * This is the main entry point for documentation generation. The command can be
 * configured through:
 * - Command line arguments and options
 * - A configuration file (reference.php by default)
 *
 * The generation process includes:
 * 1. Loading and merging configuration from file and CLI
 * 2. Indexing all classes and functions in the namespace
 * 3. Generating the API summary page
 * 4. Generating namespace-level index pages
 * 5. Generating individual pages for classes, methods, and properties
 *
 * @see Config For configuration management
 * @see Execution For the documentation generation orchestration
 */
#[AsCommand(
    name: 'generate:documentation',
    description: 'Generate API documentation for a PHP namespace',
)]
class GenerateDocumentationCommand extends Command
{
    /**
     * Styled I/O helper for formatted console output.
     */
    protected readonly SymfonyStyle $io;

    /**
     * The resolved output directory path.
     */
    protected readonly string $outputDir;

    /**
     * Whether to append to existing documentation instead of replacing it.
     */
    protected readonly bool $appendOutput;

    /**
     * Whether the user confirmed the operation (only relevant in interactive mode).
     */
    protected bool $confirmed = true;

    /**
     * Configuration manager instance.
     */
    protected Config $config;

    /**
     * Execution orchestrator instance.
     */
    protected readonly Execution $execution;

    /**
     * Configures the command arguments and options.
     *
     * Sets up:
     * - namespace argument: The PHP namespace to document
     * - --append/-a: Don't clean output directory before generating
     * - --output/-o: Output directory path
     * - --api: API definition strategy to use
     * - --config/-c: Path to configuration file
     * - --index-file-name: Name for index files
     * - --source-url-base: Base URL for source code links
     */
    protected function configure(): void
    {
        $this
            ->setHelp('This command generates API documentation for a given PHP namespace by analyzing all public classes and their methods and properties. Configuration can be set in a reference.php file, with command line arguments taking priority.')
            ->addArgument(
                name: 'namespace',
                mode: InputArgument::OPTIONAL,
                description: 'The namespace to generate documentation for (overrides config file)',
            )
            ->addOption(
                name: 'append',
                shortcut: 'a',
                mode: InputOption::VALUE_NONE,
                description: "Don't clean the output directory before generating documentation, append to existing files, overwrite existing files if they exist (overrides config file)",
            )
            ->addOption(
                name: 'output',
                shortcut: 'o',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Output directory for generated documentation (overrides config file)',
            )
            ->addOption(
                name: 'api',
                mode: InputOption::VALUE_REQUIRED,
                description: \sprintf('API definition to use (overrides config file)'),
            )
            ->addOption(
                name: 'config',
                shortcut: 'c',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Path to configuration file',
                default: getcwd() . \DIRECTORY_SEPARATOR . 'reference.php',
            )
            ->addOption(
                name: 'index-file-name',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the index file to generate',
            )
            ->addOption(
                name: 'source-url-base',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'Base URL for source code links (e.g., https://github.com/user/repo/blob/main)',
            );
    }

    /**
     * Initializes the command before interaction.
     *
     * Loads the configuration file and merges CLI arguments. Also sets the
     * no-interaction flag if configured in the config file.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        // Load configuration
        $this->config = new Config($input->getOption('config'));

        if ($this->config->get('no-interaction')) {
            $input->setInteractive(false);
        }

        // Merge CLI arguments with config, CLI takes priority
        $this->config->mergeWithCliArgs([
            'namespace' => $input->getArgument('namespace'),
            'output' => $input->getOption('output'),
            'index-file-name' => $input->getOption('index-file-name'),
            'append' => $input->getOption('append'),
            'api' => $input->getOption('api'),
            'source-url-base' => $input->getOption('source-url-base'),
        ]);
    }

    /**
     * Initializes the execution context with resolved configuration.
     *
     * This method validates the output directory exists and creates the
     * Execution instance that will orchestrate the documentation generation.
     */
    protected function init(): void
    {
        $this->appendOutput = $this->config->get(key: 'append', default: false);
        $outputPath = $this->config->get(key: 'output', default: getcwd() . \DIRECTORY_SEPARATOR . 'output');
        $realOutputPath = realpath($outputPath);

        if ($realOutputPath === false) {
            error("Output directory '{$outputPath}' does not exist.");
            exit(Command::FAILURE);
        }

        $this->outputDir = $realOutputPath;
        AbstractWriter::$outputDir = $this->outputDir;

        $this->execution = new Execution(
            codeIndex: new CodeIndex($this->config->get('namespace')),
            outputDir: $this->outputDir,
            config: $this->config,
        );
    }

    /**
     * Prompts for missing required configuration in interactive mode.
     *
     * Asks for namespace and output directory if not already configured,
     * and requests confirmation before proceeding (since the output
     * directory will be erased by default).
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $currentNamespace = $this->config->get('namespace');
        if (! $currentNamespace) {
            $namespace = $this->io->ask(
                question: 'Please enter the namespace to generate documentation for',
            );
            $this->config->set('namespace', $namespace);
        }

        $currentOutput = $this->config->get('output');
        if (! $currentOutput) {
            $outputDir = $this->io->ask(
                question: 'Please enter the output directory for generated documentation',
                default: getcwd() . \DIRECTORY_SEPARATOR . 'output'
            );
            $this->config->set('output', $outputDir);
        }

        $this->confirmed = confirm(
            label: 'Output directory will be erased first, are you sure to continue?',
            default: true,
        );
    }

    /**
     * Executes the documentation generation process.
     *
     * This method coordinates the entire generation workflow:
     * 1. Validates the namespace exists and contains documentable elements
     * 2. Cleans the output directory (unless --append is set)
     * 3. Generates the API summary index
     * 4. Generates namespace pages
     * 5. Generates individual class, method, and property pages
     * 6. Reports any errors or warnings collected during generation
     *
     * @return Command::SUCCESS on success, Command::FAILURE or Command::INVALID on error
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timer = new Timer;
        $timer->start();

        $this->init();

        if (! $this->confirmed) {
            warning('Operation cancelled by user.');

            return Command::INVALID;
        }

        $this->io->title(App::getFullName());

        // Validate the namespace exists by checking if there are any classes in it
        try {
            $progress = progress(
                label: 'Analyzing namespace',
                steps: 1
            );

            $progress->advance();
            $progress->finish();

            note(\sprintf('Found %d elements to process.', \count($this->execution->mainPhpNodes)));
        } catch (\Throwable $e) {
            error("Error while analyzing namespace '{$this->execution->codeIndex->namespace}': " . $e->getMessage());
            if ($output->isVerbose()) {
                $this->io->text($e->getTraceAsString());
            }

            return Command::FAILURE;
        }

        if (empty($this->execution->mainPhpNodes)) {
            error("Namespace '{$this->execution->codeIndex->namespace}' does not exist or contains no public classes.");

            return Command::FAILURE;
        }

        $this->io->section("Generating documentation for namespace: {$this->execution->codeIndex->namespace}");

        // Clean output directory if requested
        if (! $this->appendOutput) {
            progress(
                label: 'Cleaning output directory',
                steps: 1,
                callback: function (): string {
                    $filesystem = AbstractWriter::getFlySystem();

                    foreach ($filesystem->listContents('/', false) as $item) {
                        if ($item->isDir()) {
                            $filesystem->deleteDirectory($item->path());
                        } else {
                            $filesystem->delete($item->path());
                        }
                    }

                    return 'Output directory cleaned successfully.';
                }
            );
        }

        try {
            // Generate API summary
            progress(
                label: 'Generating API summary',
                steps: 1,
                callback: fn() => $this->execution->buildIndex($this->config->get(key: 'index-file-name', default: 'readme')),
            );

            $output->write("\033[1A"); // Move cursor up one line to remove extra blank line

            // Generate namespace pages
            $progress = progress(
                label: 'Generating namespace pages',
                steps: \count($this->execution->codeIndex->namespaces),
            );

            $this->execution->buildNamespacePages(
                indexFileName: $this->config->get(key: 'index-file-name', default: 'readme'),
                afterElementCallback: fn() => $progress->advance()
            );

            $progress->finish();

            $output->write("\033[1A"); // Move cursor up one line to remove extra blank line

            // Process each class
            $progress = progress(label: 'Processing classes', steps: \count($this->execution->mainPhpNodes));

            $this->execution->buildPages(
                afterElementCallback: fn() => $progress->advance(),
            );

            $progress->finish();

            // Display error report if there are any warnings or errors
            if ($this->execution->errorCollector->hasErrors()) {
                $summary = $this->execution->errorCollector->getSummary();
                $summaryText = [];

                foreach ($summary as $level => $count) {
                    $summaryText[] = "{$level}: {$count}";
                }

                warning('Errors/warnings were collected during generation: ' . implode(' | ', $summaryText));

                if ($output->isVerbose()) {
                    note($this->execution->errorCollector->formatForConsole());
                } else {
                    note('Run with -v to see detailed error report.');
                }
            }

            $this->io->success([
                'Documentation generation completed successfully!',
                "Output directory: {$this->outputDir}",
                \sprintf('Processed %d classes.', \count($this->execution->mainPhpNodes)),
            ]);

            info(new ResourceUsageFormatter()->resourceUsage($timer->stop()));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->io->error([
                'An error occurred during documentation generation:',
                $e->getMessage(),
            ]);

            if ($output->isVerbose()) {
                $this->io->text($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}

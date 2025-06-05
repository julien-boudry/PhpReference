<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Command;

use JulienBoudry\PhpReference\App;
use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Config;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;
use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Execution;
use JulienBoudry\PhpReference\Writer\AbstractWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\warning;

#[AsCommand(
    name: 'generate:documentation',
    description: 'Generate API documentation for a PHP namespace',
)]
class GenerateDocumentationCommand extends Command
{
    protected readonly SymfonyStyle $io;
    protected readonly string $outputDir;
    protected readonly bool $appendOutput;
    protected bool $confirmed = true;
    protected Config $config;

    protected readonly Execution $execution;

    protected function configure(): void
    {
        $this
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
                name: 'all-public',
                shortcut: 'p',
                mode: InputOption::VALUE_NONE,
                description: 'Include all public classes, methods, and properties in the documentation, even those not marked with @api tags (overrides config file)',
            )
            ->addOption(
                name: 'config',
                shortcut: 'c',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Path to configuration file',
                default: getcwd() . DIRECTORY_SEPARATOR . 'reference.php',
            )
            ->setHelp('This command generates API documentation for a given PHP namespace by analyzing all public classes and their methods and properties. Configuration can be set in a reference.php file, with command line arguments taking priority.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        // Load configuration
        $configPath = $input->getOption('config');
        $this->config = new Config($configPath);

        // Merge CLI arguments with config, CLI takes priority
        $this->config->mergeWithCliArgs([
            'namespace' => $input->getArgument('namespace'),
            'output' => $input->getOption('output'),
            'append' => $input->getOption('append'),
            'all-public' => $input->getOption('all-public'),
        ]);
    }

    protected function init(InputInterface $input): void
    {
        $this->appendOutput = $this->config->get('append', false);
        $outputPath = $this->config->get('output', getcwd() . DIRECTORY_SEPARATOR . 'output');
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
            publicApiDefinition: $this->config->get('all-public', false) ? new IsPubliclyAccessible : new HasTagApi,
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $currentNamespace = $this->config->get('namespace');
        if (!$currentNamespace) {
            $namespace = $this->io->ask(
                question: 'Please enter the namespace to generate documentation for',
            );
            $this->config->set('namespace', $namespace);
        }

        $currentOutput = $this->config->get('output');
        if (!$currentOutput) {
            $outputDir = $this->io->ask(
                question: 'Please enter the output directory for generated documentation',
                default: getcwd() . DIRECTORY_SEPARATOR . 'output'
            );
            $this->config->set('output', $outputDir);
        }

        $this->confirmed = confirm(
            label: 'Output directory will be erased first, are you sure to continue?',
            default: true,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->init($input);

        if (!$this->confirmed) {
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

            note(sprintf('Found %d elements to process.', count($this->execution->mainPhpNodes)));
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
        if (!$this->appendOutput) {
            progress(label: 'Cleaning output directory', steps: 1, callback: function(): string {
                AbstractWriter::getFlySystem()->deleteDirectory('/');
                return 'Output directory cleaned successfully.';
            });
        }

        try {
            // Generate API summary
            progress(
                label: 'Generating API summary',
                steps: 1,
                callback: fn() => $this->execution->buildIndex(),
            );

            // Process each class
            $progress = progress(label: 'Processing classes', steps: count($this->execution->mainPhpNodes));

            $this->execution->buildPages(
                afterElementCallback: function () use ($progress): void {
                    $progress->advance();
                }
            );

            $progress->finish();

            $this->io->success([
                'Documentation generation completed successfully!',
                "Output directory: {$this->outputDir}",
                sprintf('Processed %d classes.', count($this->execution->mainPhpNodes))
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->io->error([
                'An error occurred during documentation generation:',
                $e->getMessage()
            ]);

            if ($output->isVerbose()) {
                $this->io->text($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}

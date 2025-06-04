<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Command;

use JulienBoudry\PhpReference\App;
use JulienBoudry\PhpReference\CodeIndex;
use JulienBoudry\PhpReference\Execution;
use JulienBoudry\PhpReference\Writer\AbstractWriter;
use JulienBoudry\PhpReference\Writer\ClassPageWriter;
use JulienBoudry\PhpReference\Writer\MethodPageWriter;
use JulienBoudry\PhpReference\Writer\PropertyPageWriter;
use JulienBoudry\PhpReference\Writer\PublicApiSummaryWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

#[AsCommand(
    name: 'generate:documentation',
    description: 'Generate API documentation for a PHP namespace',
)]
class GenerateDocumentationCommand extends Command
{
    protected readonly SymfonyStyle $io;
    protected readonly string $namespace;
    protected readonly string $outputDir;
    protected readonly bool $appendOutput;
    protected bool $confirmed = true;
    protected bool $allPublic = false;

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'namespace',
                mode: InputArgument::OPTIONAL,
                description: 'The namespace to generate documentation for',
                default: 'CondorcetPHP\\Condorcet',
            )
            ->addOption(
                name: 'append',
                shortcut: 'a',
                mode: InputOption::VALUE_NONE,
                description: "Don't clean the output directory before generating documentation, append to existing files, overwrite existing files if they exist",
            )
            ->addOption(
                name: 'output',
                shortcut: 'o',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Output directory for generated documentation',
                default: getcwd() . DIRECTORY_SEPARATOR . 'output',
            )
            ->addOption(
                name: 'all-public',
                shortcut: 'p',
                mode: InputOption::VALUE_NONE,
                description: 'Include all public classes, methods, and properties in the documentation, even those not marked with @api or @internal tags',
            )


            ->setHelp('This command generates API documentation for a given PHP namespace by analyzing all public classes and their methods and properties.');
    }

    protected function initVar(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->namespace = $input->getArgument('namespace');
        $this->appendOutput = $input->getOption('append');
        $this->allPublic = $input->getOption('all-public');

        $realOutputPath = realpath($input->getOption('output'));

        if ($realOutputPath === false) {
            error("Output directory '{$input->getOption('output')}' does not exist.");
            exit(Command::FAILURE);
        }

        $this->outputDir = $realOutputPath;

        AbstractWriter::$outputDir = $this->outputDir;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!$input->getArgument('namespace')) {
            $namespace = $this->io->ask(
                question: 'Please enter the namespace to generate documentation for',
                default: 'CondorcetPHP\\Condorcet'
            );
            $input->setArgument('namespace', $namespace);
        }

        if (!$input->getOption('output')) {
            $outputDir = $this->io->ask(
                question: 'Please enter the output directory for generated documentation',
                default: $this->outputDir
            );
            $input->setOption('output', $outputDir);
        }

        $this->confirmed = confirm(
            label: 'Output directory will be erased first, are you sure to continue?',
            default: true,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initVar($input, $output);

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

            $codeIndex = new CodeIndex($this->namespace);
            Execution::$instance = new Execution($codeIndex);

            $elements = $this->allPublic ? $codeIndex->getPublicClasses() : $codeIndex->getApiClasses();

            $progress->advance();
            $progress->finish();

            note(sprintf('Found %d elements to process.', count($elements)));
        } catch (\Throwable $e) {
            error("Error while analyzing namespace '{$this->namespace}': " . $e->getMessage());
            if ($output->isVerbose()) {
                $this->io->text($e->getTraceAsString());
            }
            return Command::FAILURE;
        }

        if (empty($elements)) {
            error("Namespace '{$this->namespace}' does not exist or contains no public classes.");
            return Command::FAILURE;
        }

        $this->io->section("Generating documentation for namespace: {$this->namespace}");

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
                callback: fn() => new PublicApiSummaryWriter($codeIndex),
            );

            // Process each class
            $progress = progress(label: 'Processing classes', steps: count($elements));

            foreach ($elements as $class) {
                // Generate class page
                new ClassPageWriter($class);

                // Generate method pages
                $methods = $this->allPublic ?
                            $class->getAllUserDefinedMethods(protected: false, private: false) :
                            $class->getAllApiMethods();

                foreach ($methods as $method) {
                    new MethodPageWriter($method);
                }

                // Generate property pages
                $properties = $this->allPublic ?
                                $class->getAllProperties(protected: false, private: false) :
                                $class->getAllApiProperties();

                foreach ($properties as $property) {
                    new PropertyPageWriter($property);
                }

                $progress->advance();
            }

            $progress->finish();

            $this->io->success([
                'Documentation generation completed successfully!',
                "Output directory: {$this->outputDir}",
                sprintf('Processed %d classes.', count($elements))
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

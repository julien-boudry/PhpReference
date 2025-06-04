<?php declare(strict_types=1);

namespace JulienBoudry\PhpReference\Command;

use JulienBoudry\PhpReference\Reflect\CodeIndex;
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

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;

#[AsCommand(
    name: 'generate:documentation',
    description: 'Generate API documentation for a PHP namespace',
)]
class GenerateDocumentationCommand extends Command
{
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
                name: 'clean',
                shortcut: 'c',
                mode: InputOption::VALUE_NONE,
                description: 'Clean the output directory before generating documentation',
            )
            ->addOption(
                name: 'output-dir',
                shortcut: 'o',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Output directory for generated documentation',
                default: AbstractWriter::OUTPUT_DIR
            )
            ->setHelp('This command generates API documentation for a given PHP namespace by analyzing all public classes and their methods and properties.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $namespace = $input->getArgument('namespace');
        $cleanOutput = $input->getOption('clean');
        $outputDir = $input->getOption('output-dir');

        $io->title('PHP Reference Documentation Generator');

        // Validate the namespace exists by checking if there are any classes in it
        try {
            $progress = progress(
                label: 'Analyzing namespace',
                steps: 1
            );

            $codeIndex = new CodeIndex($namespace);
            $elements = $codeIndex->getPublicClasses();

            $progress->advance();
            $progress->finish();

            note(sprintf('Found %d elements to process.', count($elements)));
        } catch (\Throwable $e) {
            error("Error while analyzing namespace '{$namespace}': " . $e->getMessage());
            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
            }
            return Command::FAILURE;
        }

        if (empty($elements)) {
            error("Namespace '{$namespace}' does not exist or contains no public classes.");
            return Command::FAILURE;
        }

        $io->section("Generating documentation for namespace: {$namespace}");

        // Clean output directory if requested
        if ($cleanOutput) {
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
                foreach ($class->methods as $method) {
                    new MethodPageWriter($method);
                }

                // Generate property pages
                foreach ($class->getAllApiProperties() as $property) {
                    new PropertyPageWriter($property);
                }

                $progress->advance();
            }

            $progress->finish();

            $io->success([
                'Documentation generation completed successfully!',
                "Output directory: {$outputDir}",
                sprintf('Processed %d classes.', count($elements))
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error([
                'An error occurred during documentation generation:',
                $e->getMessage()
            ]);

            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}

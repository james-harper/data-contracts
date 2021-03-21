<?php

namespace DataContracts\Console;

use DataContracts\Console\Exceptions\FileAlreadyExistsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new pattern
 */
class MakePatternCommand extends GeneratorCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'make:pattern';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('This is a command to generate a pattern')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the pattern')
            ->addArgument('opening', InputArgument::REQUIRED, 'Opening delimiter')
            ->addArgument('close', InputArgument::OPTIONAL, 'Closing delimiter');
    }

    /**
     * Executes the current command
     *
     * @param InputInterface $input
     * @param ConsoleOutput $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $name = ucfirst($input->getArgument('name'));

            if (file_exists(__DIR__ . "/Utilities/Formatting/Patterns/$name.php")) {
                throw new FileAlreadyExistsException(
                    "Skipping pattern creation for $name. Pattern already exists"
                );
            }

            $patternFile = $this->createPatternFile($name, $input);
            $output->success(":code: Successfully created $patternFile");

            $formatter = $this->updateFormatter($name);
            $output->success(":code: Successfully created $formatter");

            return Command::SUCCESS;
        } catch (FileAlreadyExistsException $e) {
            $output->warn(':lock: ' . $e->getMessage());
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create the new pattern file
     *
     * @param string $name
     * @param InputInterface $input
     * @return string
     */
    private function createPatternFile(string $name, InputInterface $input) : string
    {
        // We require at least one delimiter.
        // In the case of one, the opening and closing ones will be the same
        $open = $input->getArgument('opening');
        $close = $input->getArgument('close') ?: $open;
        $stub = $this->replacePlaceholders($this->getStub('pattern'), [
            'DummyPattern' => $name,
            'DummyOpen' => $open,
            'DummyClose' => $close,
        ]);

        return $this->writeFile(
            "console/Utilities/Formatting/Patterns/$name.php",
            $stub
        );
    }

    /**
     * Add the new pattern to the "ApplyAll" section of the formatter
     *
     * @param string $name
     * @return string
     */
    private function updateFormatter(string $name) : string
    {
        $formatter = 'console/Utilities/Formatting/Formatter.php';

        // Insert import statement
        $this->updateExistingFile(
            $formatter,
            'namespace DataContracts\Console\Utilities\Formatting;' . PHP_EOL,
            "use DataContracts\Console\Utilities\Formatting\Patterns\\$name;",
            false,
            0
        );

        // Update array of patterns
        return $this->updateExistingFile(
            $formatter,
            'protected array $patterns = [',
            "$name::class,",
            false,
            0,
            2
        );
    }
}

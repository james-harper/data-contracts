<?php

namespace DataContracts\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;
use DataContracts\Console\Utilities\Schema\Locator;
use DataContracts\Console\Utilities\Schema\Validator;

/**
 * Validate a single contract against JSON Schema specification
 */
class ValidateSingleSchemaCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'validate:schema';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Validates a JSON schema against the specification.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of schema to validate');
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
            $name = strtolower($input->getArgument('name'));
            $contract = $this->ensureExtensionIsPresent($name, '.json');

            if (!$this->doesContractExist($contract)) {
                $output->warn("$contract schema does not exist");
                if (Str::endsWith($name, 's')) {
                    $singular = $this->ensureExtensionIsPresent(
                        Str::singular($name),
                        '.json'
                    );

                    if ($this->doesContractExist($singular)) {
                        $suggestion = ConsoleOutput::styled($singular, [
                            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_BLACK,
                            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_YELLOW,
                        ]);

                        $output->writeln(PHP_EOL .'Contract names are singular by convention.');
                        $output->writeln("So we assume that you meant to search for $suggestion..." . PHP_EOL);
                        $contract = $singular;
                        // I know...
                        // "goto considered harmful... blah, blah, blah"
                        // this is a small console script (~100 lines)
                        // I think we'll be ok
                        goto validate;
                    }
                }

                return Command::FAILURE;
            }

            validate:
                $schema = new Locator($contract);
            return (new Validator)->run($schema)
                    ? Command::SUCCESS
                    : Command::FAILURE;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Make sure that string has the specified extension if it doesn't already
     *
     * @param string $string
     * @param string $extension
     * @return string
     */
    protected function ensureExtensionIsPresent(string $string, string $extension)
    {
        if (!Str::endsWith($string, $extension)) {
            return $string . $extension;
        }

        return $string;
    }

    /**
     * Check if data contract definition exists
     *
     * @param string $contract
     * @return bool
     */
    protected function doesContractExist(string $contract) : bool
    {
        return file_exists(__DIR__ . '/../data/' . $contract);
    }
}

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
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of schema to validate');
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
            $name = $input->getArgument('name');
            // Input argument required validation is being done manually so
            // that we have control over the error message.
            // This allows us to be helpful and list all of the available
            // options if 'name' is missing
            if (!$name) {
                $this->printValidationError($output);
                return Command::FAILURE;
            }

            $name = strtolower($input->getArgument('name'));
            $contract = $this->ensureExtensionIsPresent($name, '.json');

            if (!$this->doesContractExist($contract)) {
                $output->line();
                $output->warn("$contract schema does not exist");

                $name = str_replace('.json', '', $name);
                if (in_array($name, $this->getSchemasPlural())) {
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

                $this->printAvailableOptions($output);
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
     * Get list of all registered schemas, but in plural form
     *
     * @return array
     */
    protected function getSchemasPlural() : array
    {
        return array_map(function ($name) {
            $name = str_replace('.json', '', $name);
            return Str::plural($name, 2);
        }, ValidateSchemasCommand::getAllSchemas());
    }

    /**
     * Print missing argument validation error.
     * - Since there is only one possible argument, it is fine to hard-code the message
     *
     * @param ConsoleOutput $output
     * @return void
     */
    protected function printValidationError(ConsoleOutput $output) : void
    {
        $output->line();
        $message = str_pad('    Not enough arguments (missing: "name").', 80);
        $messageStyle = [
            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_WHITE,
            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_RED,
        ];
        $lineStyle = [
            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_RED,
            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_RED,
        ];
        $output->titleBlock($message, $messageStyle, $lineStyle);
        $this->printAvailableOptions($output);
    }

    /**
     * Print all registered schemas - to point user in the right direction when
     * they have entered an invalid option
     *
     * @param ConsoleOutput $output
     * @return void
     */
    protected function printAvailableOptions(ConsoleOutput $output) : void
    {
        $schemas = ValidateSchemasCommand::getAllSchemas();
        $output->line();
        $output->warn('Schema should be one of the following: ', [], true);
        foreach ($schemas as $option) {
            $output->print("- $option");
        }
        $output->line();
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

<?php

namespace DataContracts\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;
use DataContracts\Console\Utilities\Schema\Locator;
use DataContracts\Console\Utilities\Schema\Validator;

/**
 * Validate all contracts against JSON Schema specification
 */
class ValidateSchemasCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'validate:all';

    /**
     * @var array $schemas All schemas that should be validated
     */
    protected array $schemas = [
        'todo.json',
        'user.json',
    ];

    /**
     * @var array $results
     */
    protected array $results = [];

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Validates all defined JSON schemas against the specification.');
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
            $schemaData = array_map(function ($schema) {
                return new Locator($schema);
            }, $this->schemas);

            foreach ($schemaData as $schema) {
                $this->results[] = (new Validator())->run($schema);
            }

            return !in_array(false, $this->results) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }
}

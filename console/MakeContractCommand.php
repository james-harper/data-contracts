<?php

namespace DataContracts\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new data contract
 */
class MakeContractCommand extends GeneratorCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'make:contract';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Creates a new data contract')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the contract being created');
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

            $jsonFile = $this->createContractJson($name);
            $output->success(":code: Successfully created $jsonFile");

            $exampleFile = $this->createExampleJson($name);
            $output->success(":code: Successfully created $exampleFile");

            $phpFile = $this->createContractPhp($name);
            $output->success(":code: Successfully created $phpFile");

            $testFile = $this->createContractTest($name);
            $output->success(":code: Successfully updated $testFile");

            $validationFile = $this->updateValidator($name);
            $output->success(":code: Successfully updated $validationFile");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create JSON contract defintion
     *
     * @param string $name
     * @return string
     */
    private function createContractJson(string $name) : string
    {
        $stub = $this->replacePlaceholders($this->getStub('json'), [
            'DummyName' => $name,
        ]);

        $name = strtolower($name);
        return $this->writeFile("data/$name.json", $stub);
    }

    /**
     * Create example contract Implementation
     *
     * @param string $name
     * @return string
     */
    private function createExampleJson(string $name) : string
    {
        $stub = $this->replacePlaceholders($this->getStub('example'), [
            'DummyName' => "Example $name",
        ]);

        $name = strtolower($name);
        return $this->writeFile("data/examples/$name.json", $stub);
    }

    /**
     * Create PHP implementation of the contract
     *
     * @param string $name
     * @return string
     */
    private function createContractPhp(string $name) : string
    {
        $stub = $this->replacePlaceholders($this->getStub('contract'), [
            'DummyClass' => $name,
            'DummyFile' => strtolower($name),
        ]);

        return $this->writeFile("src/$name.php", $stub);
    }

    /**
     * Add a unit test for the newly created contract
     *
     * @param string $name
     * @return string
     */
    private function createContractTest(string $name) : string
    {
        $testPath = 'tests/SchemasTest.php';
        $test = implode('', [
            file_get_contents(__DIR__ . '/../' . $testPath),
            $this->getStub('test'),
        ]);

        $insertAfter = 'namespace DataContracts\Tests;' . PHP_EOL;
        $alias = $name . 'Contract';
        $import = "DataContracts\\$name as $alias" ;

        $this->addImportStatement($test, $import, $insertAfter);

        return $this->writeFile($testPath, $this->replacePlaceholders($test, [
            'DummyResource' => Str::plural(strtolower($name)),
            'DummyClass' => $alias,
        ]), false);
    }

    /**
     * Update the schema validator to include the new contract
     *
     * @param string $name
     * @return string
     */
    private function updateValidator(string $name) : string
    {
        $name = strtolower($name);

        return $this->updateExistingFile(
            'console/ValidateSchemasCommand.php',
            'protected array $schemas = [',
            "'$name.json',",
            false,
            0,
            2
        );
    }

    /**
     * Import class into PHP file
     *
     * @param string $content File to update
     * @param string $import Fully Qualified Classname being added
     * @param string $insertAfter A string to add the import after
     * @return string
     */
    private function addImportStatement(string &$content, string $import, string $insertAfter) : string
    {
        $content = str_replace(
            $insertAfter,
            $insertAfter . PHP_EOL . "use $import;",
            $content
        );

        return $content;
    }
}

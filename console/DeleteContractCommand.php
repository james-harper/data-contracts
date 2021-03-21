<?php

namespace DataContracts\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Utilities\File\PhpFileUtility;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new schema
 */
class DeleteContractCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'delete:contract';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('This is a command to delete a schema')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the schema');
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

            if (!file_exists(__DIR__ . "/../src/$name.php")) {
                $output->print("Could not find a data contract for $name");
                return Command::FAILURE;
            }

            $output->print("Removing $name Data Contract...");
            $output->titleBlock('Removing from tests...');

            $testFile = $this->removeFromTest($name);
            $output->success(":wastebasket:  Successfully removed $name from $testFile");

            $output->titleBlock('Removing JSON Schema...');

            $json = $this->removeJsonSchema($name);
            $output->success(":wastebasket:  Successfully removed $json");

            $jsonExample = $this->removeJsonExample($name);
            $output->success(":wastebasket:  Successfully removed $jsonExample");

            $this->removeFromValidator($name);

            $output->titleBlock('Removing DataContract...');

            $dataContract = $this->removeDataContract($name);
            $output->success(":wastebasket:  Successfully removed $dataContract");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Remove JSON Schema file
     *
     * @param string $name
     * @return string
     */
    protected function removeJsonSchema(string $name)
    {
        $name = strtolower($name);
        $fileName = "data/$name.json";
        $this->files->delete($fileName);
        return $fileName;
    }

    /**
     * Remove JSON example file
     *
     * @param string $name
     * @return void
     */
    protected function removeJsonExample(string $name)
    {
        $name = strtolower($name);
        $fileName = "data/examples/$name.json";
        $this->files->delete($fileName);
        return $fileName;
    }

    /**
     * Remove php DataContract file
     *
     * @param string $name
     * @return void
     */
    private function removeDataContract(string $name)
    {
        $fileName = "src/$name.php";
        $this->files->delete($fileName);
        return $fileName;
    }

    /**
     * Remove the contract from the validate all command
     *
     * @param string $name
     * @return string
     */
    protected function removeFromValidator(string $name)
    {
        $validatorFile = 'console/ValidateSchemasCommand.php';
        $jsonFile = strtolower($name) . '.json';

        // Go through file and filter out the line that adds the contract
        // to the array of contracts to validate
        $file = array_filter(file($validatorFile), function ($line) use ($jsonFile) {
            return !Str::contains($line, $jsonFile);
        });

        $contents = implode('', $file);

        return $this->writeFile($validatorFile, $contents, false);
    }

    /**
     * Remove the contract from any tests.
     *
     * @param string $name
     * @return void
     */
    protected function removeFromTest(string $name)
    {
        $testFile = 'tests/SchemasTest.php';
        $test = $this->loadFile($testFile);
        $contract = $name . 'Contract';

        // Remove any imports of this data contract
        $test = PhpFileUtility::removeImportStatement($test, $contract);

        // Get each block of code within the file
        $blocks = explode(PHP_EOL . PHP_EOL, $test);

        // Remove any blocks that are tests containing the schema in question
        $blocks = array_filter($blocks, function ($block) use ($contract) {
            return !(
                Str::startsWith($block, 'it(') && // is a test
                Str::contains($block, $contract) // contains the contract
            );
        });

        $blocks = implode(PHP_EOL . PHP_EOL, $blocks) . PHP_EOL;
        $this->writeFile($testFile, $blocks, false);

        return $testFile;
    }
}

<?php

namespace DataContracts\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new rule
 */
class MakeRuleCommand extends GeneratorCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'make:rule';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Creates a new validation rule')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the rule being created');
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

            if (file_exists(__DIR__ . "/../src/Rules/$name.php")) {
                $output->warn(":lock: Skipping rule creation for $name. Rule already exists");
                return Command::SUCCESS;
            }

            $file = $this->createRuleFile($name);
            $output->success(":code: Successfully created $file");

            $constantsFile = $this->addConstant($name);
            $output->success(":code: Successfully updated $constantsFile");

            $registryFile = $this->addToRegistry($name);
            $output->success(":code: Successfully updated $registryFile");

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
    private function createRuleFile(string $name) : string
    {
        $stub = $this->replacePlaceholders($this->getStub('rule'), [
            'DummyRule' => $name,
            'DummyHash' => strtolower($name),
            'DummyConstant' => Str::upper(Str::snake($name)),
        ]);

        return $this->writeFile("src/Rules/$name.php", $stub);
    }

    /**
     * Add constant to LaravelRule file
     *
     * @param string $name
     * @return string
     */
    private function addConstant(string $name)
    {
        $name = Str::snake($name);
        $upper = Str::upper($name);
        $lower = Str::lower($name);
        $statement = "const $upper = '$lower';";

        return $this->updateExistingFile(
            'src/Rules/LaravelRule.php',
            '}',
            $statement
        );
    }

    /**
     * Import rule class into rules registry
     *
     * @param string $name Rule name
     * @return string
     */
    private function addToRegistry(string $name) : string
    {
        return $this->updateExistingFile(
            'src/Rules/RulesRegistry.php',
            '];',
            "$name::class,",
            true,
            1,
            1
        );
    }
}

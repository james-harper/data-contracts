<?php

namespace DataContracts\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DataContracts\Console\Exceptions\FileAlreadyExistsException;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new command
 */
class MakeCommandCommand extends GeneratorCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'make:command';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Creates a new generator command')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of command being created');
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

            $command = $this->createCommandFile($name);
            $output->success(":code: Successfully created $command");

            $helpFile = $this->createHelpFile($name);
            $output->success(":code: Successfully created $helpFile");

            $registerFile = $this->registerCommand($name);
            $output->success(":code: Successfully updated $registerFile");

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
     * Create JSON contract defintion
     *
     * @param string $name
     * @return string
     */
    private function createCommandFile(string $name) : string
    {
        $isGenerator = Str::startsWith($name, 'Make');
        [,$noun] = $this->getVerbAndNoun($name);
        $execStub = $isGenerator ? 'generator-execute' : 'process-execute';

        $stub = $this->replacePlaceholders($this->getStub('command'), [
            'DummyCommand' => $name,
            'DummyName' => $noun,
            'DummyBaseClass' => $isGenerator ? 'GeneratorCommand' : 'BaseCommand',
            'DummyTitle' => $this->getTrigger($name, $isGenerator),
            'DummyHelp' => $this->getHelpMessage($name, $isGenerator),
        ]);

        $stub = $this->updateImports($stub, $isGenerator);
        $stub = $this->injectExecuteMethod($stub, $execStub);
        return $this->writeFile("console/$name.php", $stub);
    }

    /**
     * Create markdown help file
     *
     * @param string $name
     * @return string
     */
    private function createHelpFile(string $name) : string
    {
        $fileName = $name;
        if (Str::endsWith($fileName, 'Command')) {
            $fileName = substr($fileName, 0, strlen($fileName) - 7);
        }

        return $this->writeFile(
            "console/docs/$fileName.md",
            $this->getHelpMessage($name, Str::startsWith($name, 'Make'))
        );
    }

    /**
     * Register the command in the CLI so it can actually be run
     *
     * @param string $name
     * @return string
     */
    private function registerCommand(string $name) : string
    {
        [,$noun] = $this->getVerbAndNoun($name);

        return $this->updateExistingFile(
            'console/Kernel.php',
            '];',
            "$name::class => ['$noun'],",
            true,
            1,
            1
        );
    }

    /**
     * Get additional imports for command depending on whether or not
     * it is a generator class
     *
     * @param boolean $isGenerator
     * @return string
     */
    private function updateImports(string $stub, bool $isGenerator) : string
    {
        $imports = ($isGenerator) ? [
            'use DataContracts\Console\Exceptions\FileAlreadyExistsException;',
        ] : [
            'use Symfony\Component\Process\Process;',
            'use Symfony\Component\Process\Exception\ProcessFailedException;',
        ];

        $imports = implode(PHP_EOL, $imports);
        return $this->updateExistingContent(
            $stub,
            'DummyImports',
            $imports,
            true,
            0,
            0,
            true
        );
    }

    /**
     * Replace the body of the execute method with content that depends
     * on the type of new stub
     *
     * @param string $content
     * @param string $stubPath
     * @param int $indexLevel
     * @return string
     */
    private function injectExecuteMethod(string $content, string $stubPath, int $indentLevel = 2) : string
    {
        // Since execute() is a method within a class
        // we need to go through line by line and apply the appropriate
        // amount of indentation for the formatting to not look odd.
        $file = array_map(function ($line) use ($indentLevel) {
            return $this->indent($indentLevel) . $line;
        }, file(__DIR__ . '/stubs/' . $stubPath . '.stub'));

        // The indentation can be removed from the first line as the placeholder
        // that it replaces is already at the perfect indentation level.
        $file[0] = ltrim($file[0]);
        return str_replace('DummyExecute', implode('', $file), $content);
    }

    /**
     * Get shortened version of name that summarises what the command does
     *
     * @param string $name
     * @return string
     */
    private function getCommandType(string $name) : string
    {
        $type = str_replace('Make', '', $name);
        return str_replace('Command', '', $type);
    }

    /**
     * Get the input that will be used to trigger the command
     *
     * @param string $name
     * @param bool $isGenerator
     * @return string
     */
    private function getTrigger(string $name, bool $isGenerator) : string
    {
        $type = $this->getCommandType($name);

        // For generator commands, the convention is `make:thing`
        if ($isGenerator) {
            $type = strtolower($type);
            return "make:$type";
        }

        // In other cases, the sensible default is to just
        // take each words and join them with a colon.
        //Eg DoSomething => do:something
        $words = preg_split('/(?=[A-Z])/', $type);
        $words = array_filter($words);
        return strtolower(implode(':', $words));
    }

    /**
     * Get the default help message
     *
     * @param string $name
     * @param bool $isGenerator
     * @return string
     */
    private function getHelpMessage(string $name, bool $isGenerator) : string
    {
        [$verb, $noun] = $this->getVerbAndNoun($name);
        if ($isGenerator) {
            $verb = 'generate';
        }

        return "This is a command to $verb a $noun";
    }

    /**
     * Tries to figure out Verb & Noun for Commands name
     *
     * @param string $name
     * @return array
     */
    private function getVerbAndNoun(string $name) : array
    {
        $trigger = $this->getTrigger($name, false);
        $words = new Collection(explode(':', $trigger));
        return [
            $words->first(),
            $words->last(),
        ];
    }
}

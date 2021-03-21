<?php

namespace DataContracts\Console;

use Symfony\Component\Console\Application;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * CLI Kernel
 */
class Kernel
{
    /**
     * Key: Command class
     * Value: Alternate aliases that can be used to run the command
     *
     * @var array $commands Available commands
     */
    protected array $commands = [
        FlushCacheCommand::class => ['flush', 'cache:clear', 'clear:cache'],
        MakeCommandCommand::class => ['command', 'generate'],
        MakeContractCommand::class => ['new', 'contract', 'schema', 'make'],
        MakeRuleCommand::class => ['rule', 'validation'],
        ValidateSingleSchemaCommand::class => ['validate', 'schema:validate', 'check'],
        ValidateSchemasCommand::class => ['validate-all'],
        RunTestSuiteCommand::class => ['test', 'pest', 'run:test', 'test:group'],
        RunLinterCommand::class => ['lint', 'cs-fixer'],
        MakePatternCommand::class => ['pattern', 'formatting', 'make:formatting'],
        DeleteContractCommand::class => ['delete', 'remove', 'delete:schema'],
    ];

    /** @var Application $app Console application instance */
    private Application $app;

    /**
     * Kernel constructor
     *
     * @param Application $application
     */
    public function __construct(Application $application = null)
    {
        $this->app = $application ?? new Application;
    }

    /**
     * Load commands so that they can be run in the CLI
     *
     * @return void
     */
    public function loadCommands()
    {
        foreach ($this->commands as $name => $aliases) {
            $command = (new $name)
                ->setAliases($aliases)
                ->setDocs();
            $this->app->add($command);
        }
    }

    /**
     * Run the CLI Application
     *
     * @return int
     */
    public function runApplication()
    {
        return $this->app->run(null, new ConsoleOutput());
    }
}

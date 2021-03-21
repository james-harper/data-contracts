<?php

namespace DataContracts\Console;

use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;
use DataContracts\Tests\Constants\Group;

/**
 * Command for Tests (or groups of tests)
 */
class RunTestSuiteCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'test:run';

    /** @var array $warnings */
    protected array $warnings = [];

    /** @var array $lineStyle */
    protected array $lineStyle = [
        ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_GRAY,
    ];

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $groups = array_map(function ($group) {
            return "`$group`";
        }, Group::all());

        $this->setDescription('Runs unit tests')
            ->setHelpMultiline([
                'This command runs the unit tests for the application.',
                '',
                'A {group} (or a comma separated list of multple {groups}) can be',
                'passed as an optional argument to only run the specified',
                '{group}(s) of tests.',
                '',
                'The following groups are permitted: ',
                implode(', ', $groups),
            ])
            ->addArgument('group', null, InputArgument::OPTIONAL, '');
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
            $args = $this->applyGroups(['vendor/bin/pest'], $input);
            $process = new Process($args);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $output->write($process->getOutput());
            $output->success(':tada: Tests ran successfully!');
            $this->printWarnings($output);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    private function label(string $text)
    {
        return ConsoleOutput::styled($text, [
            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_YELLOW,
            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_DEFAULT,
        ]);
    }

    /**
     * If any group arguments have been provided
     * Filter tests by group
     *
     * @param array $args
     * @param InputInterface $input
     * @return array
     */
    private function applyGroups(array $args, InputInterface $input)
    {
        $group = $input->getArgument('group');
        if ($group !== '') {
            $groups = new Collection(explode(',', $group));
            $groups->filter(function ($group) {
                return !in_array($group, Group::all());
            })->each(function ($group) {
                $name = $this->label('Group does not exist:');
                $this->warnings[] = "$name $group";
            });

            $args[] = "--group=$group";
        }

        return $args;
    }

    /**
     * Print any warnings that were found to the console
     *
     * @param ConsoleOutput $output
     * @return void
     */
    private function printWarnings(ConsoleOutput $output)
    {
        $count = count($this->warnings);
        if (!$count) {
            return;
        }

        $title = ($count === 1) ?
             '1 warning was found' : "$count warnings were found";

        // Warnings header
        $output->titleBlock("• $title •", [
            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_BRIGHT_WHITE,
            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_BLACK,
        ], $this->lineStyle);

        // Warnings body
        foreach ($this->warnings as $warning) {
            $output->writeln($warning);
        }

        // Warnings info block
        $label = $this->label('The following groups are permitted:');
        $info  = $label . ' ' . implode(', ', Group::all());
        $output->line('-', $this->lineStyle);
        $output->writeln($info);
        $output->line('-', $this->lineStyle);
    }
}

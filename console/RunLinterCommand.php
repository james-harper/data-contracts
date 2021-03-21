<?php

namespace DataContracts\Console;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new linter
 */
class RunLinterCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'lint:run';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('This is a command to run a linter')
            ->addOption(
                'fix',
                null,
                InputOption::VALUE_NONE,
                'Skip dry-run and run fix straight away'
            );
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
            if ($input->getOption('fix')) {
                // if --fix option is passed, user prompt is unnecessary
                return $this->applyFixes($output);
            }

            // Run command with dry-run flag first
            // So user can decide if they actually want to
            // perform any updates or not
            $dryRun = $this->getLintProcess(true);
            $dryRun->run();

            if ($dryRun->isSuccessful()) {
                $output->success(':+1: No linting errors found. Good job');
                return Command::SUCCESS;
            }

            $this->lintPreview($output, $dryRun);

            $result = $this->getConfirmationPrompt(
                'Would you like to apply the fixes now? (y/n): ',
                function () use ($output) {
                    return $this->applyFixes($output);
                },
                $input,
                $output
            );

            return $result;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Apply fixes to any linting errors that were found
     *
     * @param ConsoleOutput $output
     * @return int
     */
    private function applyFixes(ConsoleOutput $output)
    {
        $process = $this->getLintProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output->warn(':-1: Linting unsuccessful:');
            $output->writeLn($process->getOutput());
            return Command::FAILURE;
        }

        $lintOutput = $process->getOutput();
        // 1) is from the list of errors that get displayed after a
        // successful fix.
        // It is possible to skip to this point by using --fix flag
        // so we check if the list gets outputted so we
        // can provide a better (& less confusing) user experience
        if (!Str::contains($lintOutput, '1)')) {
            $output->warn('No lint errors to fix.', [], true);
        } else {
            $output->success(':wrench: Successfully applied the following fixes:');
            $output->writeln($lintOutput);
        }

        $output->print(':wave: Goodbye!');
        return Command::SUCCESS;
    }

    /**
     * Get the php-cs-fixer linting command
     *
     * @param boolean $isDryRun
     * @return Process
     */
    protected function getLintProcess(bool $isDryRun = false) : Process
    {
        $args = ($isDryRun) ? ['composer', 'lint'] : ['composer', 'lint:fix'];
        return new Process($args);
    }

    /**
     * Print dry-run errors to the console
     *
     * @param InputInterface $input
     * @param ConsoleOutput $output
     * @param Process $process
     * @return void
     */
    protected function lintPreview(ConsoleOutput $output, Process $process)
    {
        $titleStyle = [
            ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_BRIGHT_GREEN,
            ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_BLACK,
        ];

        $output->titleBlock(str_pad('- Lint Errors Detected ', 80), $titleStyle, $titleStyle);
        $output->warn($process->getOutput(), [], true);
    }
}

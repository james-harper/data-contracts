<?php

namespace DataContracts\Console;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Create a new changelog
 */
class UpdateChangelogCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'update:changelog';

    /**
     * @var array $conventionalCommits
     */
    protected array $conventionalCommits = [
        'feat' => ':sparkles:',
        'fix' => ':bug:',
        'docs' => ':books:',
        'style' => ':lipstick:',
        'refactor' => ':hammer:',
        'perf' => ':rocket:',
        'test' => ':rotating_light:',
        'build' => ':package:',
        'ci' => ':construction_worker:',
        'chore' => ':wrench:',
        'revert' => ':back:',
    ];

    /**
     * Commit types that should be added to the "Changed" section
     * @var array $changed
     */
    protected array $changed = [
        'refactor',
        'perf',
        'revert',
        'style',
    ];

    /**
     * @var array $log       Array representation of the changelog.
     *                       Entries can be added by pushing to the array.
     *                       It will get collapsed down to a string when it is
     *                       eventually written to file.
     */
    protected array $log = [];

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('This is a command to update a changelog');
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
            // git log with some formatting options applied
            $process = new Process([
                'git',
                'log',
                "--pretty=format:'%ad | %h | %s'",
                '--date=short',
            ]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $gitLog = explode(PHP_EOL, $process->getOutput());
            $gitLog = array_map(function ($entry) {
                // This is possible due to the `git log` format that has been specified
                [$date, $hash, $message] = explode(' | ', $entry);

                $message = ltrim($message);
                $message = trim($message, '\'');
                // If the message starts with a conventional commit type,
                // We can swap out the text with an emoji to make things look a bit nicer
                if (Str::startsWith($message, array_keys($this->conventionalCommits))) {
                    [$type, $message] = explode(':', $message);
                    $emoji = $this->conventionalCommits[$type];
                    $message = "$emoji $message";
                } else {
                    // There shouldn't be many messages not using conventional commit prefixes,
                    // but use a default emoji so these messages don't look too out of place
                    $message = ":code: $message";
                }

                return [
                    'date' => trim($date, '\''),
                    'hash' => $hash,
                    'message' => "- $message",
                    'type' => $type ?? 'unknown',
                ];
            }, $gitLog);

            $this->log = $this->getHeading();
            // Grouping by commit date because it is the most sensible thing
            // that I currently have access to.
            // Tag or Release could potentially be used for grouping in future.
            $groupedLog = (new Collection($gitLog))->groupBy('date');
            foreach ($groupedLog as $date) {
                $this->addDate($date);
            }

            $logContent = implode(PHP_EOL, $this->log);
            $this->writeFile('CHANGELOG.md', $logContent, false);
            $output->success(':white_checkmark: Successfully updated CHANGELOG.md');
            $output->line();
            $output->print($logContent);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Add changelog entries for the given date
     *
     * @param Collection $date Git commits grouped by date
     * @return array
     */
    private function addDate(Collection $date) : array
    {
        $dateString = $date->first()['date'];
        $this->log[] = "## [1.0.0] - $dateString";
        $this->log[] = '';

        $added = [];
        $fixed = [];
        $changed = [];

        // Sort by category
        foreach ($date->all() as $entry) {
            if ($entry['type'] === 'fix') {
                $fixed[] = $entry;
            }

            if (in_array($entry['type'], $this->changed)) {
                $changed[] = $entry;
            } else {
                $added[] = $entry;
            }
        }

        $this->addSubsection('Added', $added);
        $this->addSubsection('Changed', $changed);
        $this->addSubsection('Fixed', $fixed);

        return $this->log;
    }

    /**
     * Add a changelog subsection
     * Eg. Added, Changed, Fixed, Removed
     *
     * @param string $type
     * @param array $data
     * @return array
     */
    private function addSubsection(string $type, array $data)
    {
        if (count($data)) {
            $this->log[] = "### $type";
            $this->log[] = '';
            foreach ($data as $entry) {
                $this->log[] = $entry['message'];
            }
            $this->log[] = '';
        }

        return $this->log;
    }

    /**
     * Get CHANGELOG.md heading
     *
     * @return array
     */
    private function getHeading() : array
    {
        return [
            '# Changelog',
            '',
            'All notable changes to this project will be documented in this file.',
            'The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)',
            '',
        ];
    }
}

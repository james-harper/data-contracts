<?php

namespace DataContracts\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;
use DataContracts\Cache\FileCache;

/**
 * Command for flushing the cache
 */
class FlushCacheCommand extends BaseCommand
{
    /**
     * The name of the command
     *
     * @var string
     */
    protected static $defaultName = 'flush:cache';

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure() : void
    {
        $this->setDescription('Flushes the cache.');
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
            $cache = FileCache::make();
            $cache->clear();
            $output->success(':toilet: Successfully flushed cache.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->danger($e->getMessage());
            return Command::FAILURE;
        }
    }
}

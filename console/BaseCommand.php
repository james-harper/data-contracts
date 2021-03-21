<?php

namespace DataContracts\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use DataContracts\Console\Exceptions\FileAlreadyExistsException;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;
use DataContracts\Console\Utilities\Formatting\Emoji;
use DataContracts\Console\Utilities\Formatting\Formatter;

/**
 * Base command
 */
abstract class BaseCommand extends Command
{
    /** @var Filesystem $files Filesystem instance */
    protected $files;

    /**
     * Command constructor
     */
    public function __construct()
    {
        $this->files = new Filesystem();
        parent::__construct();
    }

    /**
     * Get the contents of a file
     *
     * @param string $path Relative to project root
     * @return string
     */
    protected function loadFile(string $path) : string
    {
        return $this->files->get(__DIR__ . '/../' . $path);
    }

    /**
     * Write a file to the filesystem
     *
     * @param string $path Path relative to project root
     * @param string $contents File content
     * @param bool $isNewFile Is the file being created new
     * @return string Returns the path of the newly created file
     * @throws FileAlreadyExistsException
     */
    protected function writeFile(
        string $path,
        string $contents,
        bool $isNewFile = true
    ) : string {
        $path = __DIR__  .'/../' . $path;

        if ($isNewFile && file_exists($path)) {
            throw new FileAlreadyExistsException(
                "Skipping creation of $path. File already exists"
            );
        }

        $this->files->put($path, $contents);
        return str_replace('console/../', '', $path);
    }

    /**
     * Add indention to generated code
     *
     * @param integer $level
     * @return string
     */
    protected function indent(int $level = 1) : string
    {
        if ($level === 0) {
            return '';
        }

        return str_repeat('    ', $level);
    }

    /**
     * Helper function for setting long help messages
     *
     * @param array $lines
     * @return $this
     */
    protected function setHelpMultiline(array $lines)
    {
        $text = implode(PHP_EOL, $lines);
        return $this->setHelp((new Formatter)->applyAll($text));
    }

    /**
     * Associate the command with the appropriate Help markdown file
     *
     * @return self
     */
    public function setDocs() : self
    {
        // Remove namespace from start and 'Command' from end of name
        // and include markdown extension
        $helpFile = str_replace(__NAMESPACE__ . '\\', '', static::class);
        $helpFile = substr($helpFile, 0, strlen($helpFile) - 7);
        $helpFile = __DIR__ . '/docs/' . $helpFile . '.md';

        if ($this->files->exists($helpFile)) {
            $help = $this->files->get($helpFile);
            $help = (new Formatter)->applyAll($help);
            $help = Emoji::insertEmojis($help);
            $this->setHelp($help);
        }

        return $this;
    }

    /**
     * Ask the user for confirmation before running a callback
     *
     * @param string $question
     * @param callable $onConfirm
     * @param InputInterface $input
     * @param ConsoleOutput $output
     * @return integer
     */
    protected function getConfirmationPrompt(
        string $question,
        callable $onConfirm,
        InputInterface $input,
        ConsoleOutput $output
    ) {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($question, false);

        if ($helper->ask($input, $output, $question)) {
            return $onConfirm($output);
        }

        return Command::FAILURE;
    }
}

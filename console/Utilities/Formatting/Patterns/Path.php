<?php

namespace DataContracts\Console\Utilities\Formatting\Patterns;

use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Path console formatting
 */
class Path implements PatternInterface
{
    /**
     * Regex to check if pattern should be applied.
     * This is using a backticks to denote a path
     *
     * @return string
     */
    public function getRegex() : string
    {
        return '/`([0-9A-Za-z_ \\\\\/\.\#\<>^:-]+)`/';
    }

    /**
     * Get the styles that should be applied when the pattern matches
     *
     * @return array
     */
    public function getStyles() : array
    {
        return [
            ConsoleOutput::FOREGROUND =>  ConsoleOutput::COLOUR_GRAY,
            ConsoleOutput::BACKGROUND =>  ConsoleOutput::COLOUR_DEFAULT,
            ConsoleOutput::OPTIONS =>  [
                ConsoleOutput::OPTION_UNDERSCORE,
            ],
        ];
    }


    /**
     * Get the delimiters used to separate the highlighted word from the rest
     * of the text
     *
     * @return array
     */
    public function getDelimiters() : array
    {
        return ['`'];
    }
}

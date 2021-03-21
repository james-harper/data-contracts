<?php

namespace DataContracts\Console\Utilities\Formatting\Patterns;

use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Underline console formatting pattern
 */
class Underline implements PatternInterface
{
    /**
     * Regex to check if pattern should be applied.
     * This is using underscores _ (similar to markdown)
     *
     * @return string
     */
    public function getRegex() : string
    {
        return '/_([0-9A-Za-z_\\\\\/ :]+)_/';
    }

    /**
     * Get the styles that should be applied when the pattern matches
     *
     * @return array
     */
    public function getStyles() : array
    {
        return [
            ConsoleOutput::OPTIONS =>  [
                ConsoleOutput::OPTION_UNDERSCORE,
            ],
        ];
    }

    /**
     * Get the delimiters used to separate the underlined text
     *
     * @return array
     */
    public function getDelimiters() : array
    {
        return ['_'];
    }
}

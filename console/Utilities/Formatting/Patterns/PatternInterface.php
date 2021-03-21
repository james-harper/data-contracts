<?php

namespace DataContracts\Console\Utilities\Formatting\Patterns;

/**
 * A Pattern that can be used to apply formatting to console output
 * (Similar to markdown)
 */
interface PatternInterface
{
    /**
     * Get the regex used to see if the formatting should be applied
     *
     * @return string
     */
    public function getRegex() : string;

    /**
     * Get the styles that should be applied when the regex matches
     *
     * @return array
     */
    public function getStyles() : array;

    /**
     * Get the delimiters used to separate the selected text from the rest
     *
     * @return array
     */
    public function getDelimiters() : array;
}

<?php

namespace DataContracts\Console\Utilities\Formatting\Patterns;

/**
 * A general purpose pattern that can be used for creating new formatting patterns
 */
class Pattern implements PatternInterface
{
    /** @var string $regex */
    protected string $regex;
    /** @var array $styles */
    protected array $styles;
    /** @var array $delimiters */
    protected array $delimiters;

    /**
     * Pattern constuctor
     *
     * @param string $regex
     * @param array $styles
     * @param array $delimiters
     */
    public function __construct(string $regex, array $styles, array $delimiters)
    {
        $this->regex = $regex;
        $this->styles = $styles;
        $this->delimiters = $delimiters;
    }

    /**
     * Regex to check if pattern should be applied.
     *
     * @return string
     */
    public function getRegex() : string
    {
        return $this->regex;
    }

    /**
     * Get the styles that should be applied when the pattern matches
     *
     * @return array
     */
    public function getStyles() : array
    {
        return $this->styles;
    }

    /**
     * Get the delimiters used to separate the highlighted word from the rest
     * of the text
     *
     * @return array
     */
    public function getDelimiters() : array
    {
        return $this->delimiters;
    }
}

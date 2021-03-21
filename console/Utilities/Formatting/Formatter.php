<?php

namespace DataContracts\Console\Utilities\Formatting;

use DataContracts\Console\Utilities\Formatting\Patterns\BrightHighlight;
use DataContracts\Console\Utilities\Formatting\Patterns\Highlight;
use DataContracts\Console\Utilities\Formatting\Patterns\Path;
use DataContracts\Console\Utilities\Formatting\Patterns\PatternInterface;
use DataContracts\Console\Utilities\Formatting\Patterns\Underline;

/**
 * Class for applying formatting to console text
 */
class Formatter
{
    /**
     * @var array $patterns Patterns that are enabled
     */
    protected array $patterns = [
        BrightHighlight::class,
        Highlight::class,
        Path::class,
        Underline::class,
    ];

    /**
     * Apply the specified pattern to the specified text
     *
     * @param string $text
     * @param PatternInterface $pattern
     * @return string The formatted text
     */
    public function apply(string $text, PatternInterface $pattern) : string
    {
        [$open, $close] = $this->getDelimiters($pattern);

        preg_match_all($pattern->getRegex(), $text, $matches);

        foreach ($matches[1] as $match) {
            $find = $open . $match . $close;
            $text = str_replace(
                $find,
                ConsoleOutput::styled($match, $pattern->getStyles()),
                $text
            );
        }

        return $text;
    }

    /**
     * Apply all formatting patterns to the given text
     *
     * @param string $text
     * @return string
     */
    public function applyAll(string $text) : string
    {
        foreach ($this->patterns as $pattern) {
            $text = $this->apply($text, new $pattern);
        }

        return $text;
    }

    /**
     * Get the delimiters used to mark the affected text
     *
     * @param PatternInterface $pattern
     * @return array
     */
    private function getDelimiters(PatternInterface $pattern) : array
    {
        $delimiters = $pattern->getDelimiters();
        $opening = $delimiters[0];
        $closing = count($delimiters) > 1 ? $delimiters[1] : $opening;

        return [$opening, $closing];
    }
}

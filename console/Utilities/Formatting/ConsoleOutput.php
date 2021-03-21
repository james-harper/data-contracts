<?php

namespace DataContracts\Console\Utilities\Formatting;

use Symfony\Component\Console\Output\ConsoleOutput as BaseOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput extends BaseOutput implements OutputInterface
{
    const MAX_WIDTH_IDEAL = 80;
    const FOREGROUND = 'fg';
    const BACKGROUND = 'bg';
    const OPTIONS = 'options';

    const COLOUR_BLACK = 'black';
    const COLOUR_BRIGHT_GREEN = 'bright-green';
    const COLOUR_BRIGHT_WHITE = 'bright-white';
    const COLOUR_BRIGHT_YELLOW = 'bright-yellow';
    const COLOUR_DEFAULT = 'default';
    const COLOUR_GRAY = 'gray';
    const COLOUR_GREEN = 'green';
    const COLOUR_RED = 'red';
    const COLOUR_WHITE = 'white';
    const COLOUR_YELLOW = 'yellow';

    const OPTION_BOLD = 'bold';
    const OPTION_UNDERSCORE = 'underscore';

    const DEFAULT_STYLE = [
        ConsoleOutput::FOREGROUND => ConsoleOutput::COLOUR_DEFAULT,
        ConsoleOutput::BACKGROUND => ConsoleOutput::COLOUR_DEFAULT,
    ];

    /**
     * Wrap a string in console styling
     *
     * @param string $text
     * @param array $styles Key-value pairs of styles
     * @return string
     */
    public static function styled(string $text, array $styles) : string
    {
        $tags = '';
        foreach ($styles as $k => $v) {
            if (is_array($v)) {
                $v = implode(',', $v);
            }

            $tags = $tags . "$k=$v;";
        }

        $text = (new Formatter)->applyAll($text);
        $text = Emoji::insertEmojis($text);
        return "<$tags>$text</>";
    }

    /**
     * Print a success message
     *
     * @param string $message
     * @param array $overrides Any additional styles that should be applied
     * @return void
     */
    public function success(string $message, array $overrides = [])
    {
        $styles = array_merge([
            self::FOREGROUND => self::COLOUR_BRIGHT_GREEN,
            self::BACKGROUND => self::COLOUR_DEFAULT,
        ], $overrides);

        return $this->writeln(self::styled($message, $styles));
    }

    /**
     * Print a danger message
     *
     * @param string $message
     * @param array $overrides Any additional styles that should be applied
     * @return void
     */
    public function danger(string $message, array $overrides = [])
    {
        $styles = array_merge([
            self::FOREGROUND => self::COLOUR_WHITE,
            self::BACKGROUND => self::COLOUR_RED,
        ], $overrides);

        return $this->writeln(self::styled($message, $styles));
    }

    /**
     * Print a warning message
     *
     * @param string $message
     * @param array $overrides Any additional styles that should be applied
     * @param bool $invert
     * @return void
     */
    public function warn(string $message, array $overrides = [], bool $invert = false)
    {
        $styles = array_merge([
            self::FOREGROUND =>
                $invert ? self::COLOUR_YELLOW : self::COLOUR_BLACK,
            self::BACKGROUND =>
                $invert ? self::COLOUR_DEFAULT : self::COLOUR_YELLOW,
        ], $overrides);

        return $this->writeln(self::styled($message, $styles));
    }

    /**
     * Print a message
     *
     * @param string $message
     * @param array $overrides
     * @return void
     */
    public function print(string $message, array $overrides = self::DEFAULT_STYLE)
    {
        $styles = array_merge(self::DEFAULT_STYLE, $overrides);
        return $this->writeln(self::styled($message, $styles));
    }

    /**
     * Output a title block to the console
     *
     * @param string $text
     * @param array $textStyles
     * @param array $lineStyles
     * @return void
     */
    public function titleBlock(string $text, array $textStyles = self::DEFAULT_STYLE, array $lineStyles = self::DEFAULT_STYLE)
    {
        $textStyles = array_merge([], $textStyles);
        $lineStyles = array_merge([], $lineStyles);

        $this->line('-', $lineStyles);
        $this->writeln(self::styled($text, $textStyles));
        $this->line('-', $lineStyles);
    }

    /**
     * Output a line in the console
     *
     * @param string $char
     * @param array $styles
     * @return void
     */
    public function line(string $char = '-', array $styles = [])
    {
        $numRepeats = self::MAX_WIDTH_IDEAL / strlen($char);
        $line = str_repeat($char, $numRepeats);

        if (count($styles)) {
            $line = self::styled($line, $styles);
        }

        $this->writeln($line);
    }
}

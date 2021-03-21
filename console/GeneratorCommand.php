<?php

namespace DataContracts\Console;

/**
 * Base command for generator commands
 */
abstract class GeneratorCommand extends BaseCommand
{
    /**
     * Insert new content into an existing file
     *
     * @param string $path Path to file relative to project root
     * @param string $needle The place the updated content should be inserted
     * @param string $newContent The newly inserted content
     * @param bool $insertBefore Should new content be inserted before or after the needle?
     * @param integer $indentBefore How many levels of indentation before the inserted content?
     * @param integer $indentAfter How many levels of indentation on the line after the inserted content?
     *                This will usually be one level less than $indentBefore, but not always.
     * @param bool $removeNeedle Should the placeholder be removed after the new content is inserted
     * @return string The path to the updated file
     */
    protected function updateExistingFile(
        string $path,
        string $needle,
        string $newContent,
        bool $insertBefore = true,
        int $indentBefore = 1,
        int $indentAfter = 0,
        bool $removeNeedle = false
    ) {
        $file = $this->loadFile($path);
        $updated = $this->updateExistingContent(
            $file,
            $needle,
            $newContent,
            $insertBefore,
            $indentBefore,
            $indentAfter,
            $removeNeedle
        );
        return $this->writeFile($path, $updated, false);
    }

    /**
     * Insert new content into an existing string
     *
     * @param string $content The content being updated
     * @param string $needle The place the updated content should be inserted
     * @param string $newContent The newly inserted content
     * @param bool $insertBefore Should new content be inserted before or after the needle?
     * @param integer $indentBefore How many levels of indentation before the inserted content?
     * @param integer $indentAfter How many levels of indentation on the line after the inserted content?
     *                This will usually be one level less than $indentBefore, but not always.
     * @param bool $removeNeedle Should the placeholder be removed after the new content is inserted
     * @return string The updated string
     */
    protected function updateExistingContent(
        string $content,
        string $needle,
        string $newContent,
        bool $insertBefore = true,
        int $indentBefore = 1,
        int $indentAfter = 0,
        bool $removeNeedle = false
    ) {
        $afterNeedle = $removeNeedle ? '' : $needle;
        $glue = $removeNeedle ? '' : PHP_EOL;
        $before = ($insertBefore) ? $newContent : $afterNeedle;
        $after =  ($insertBefore) ? $afterNeedle : $newContent;

        $updated = str_replace(
            $needle,
            $this->indent($indentBefore) . $before . $glue . $this->indent($indentAfter) . $after,
            $content
        );

        return $updated;
    }

    /**
     * Replace any placeholders in a stub
     *
     * @param string $stub Contents of a stub file
     * @param array $substitutions
     * @return string
     */
    protected function replacePlaceholders(
        string $stub,
        array $substitutions = []
    ) : string {
        foreach ($substitutions as $find => $replace) {
            $stub = str_replace($find, $replace, $stub);
        }

        return $stub;
    }

    /**
     * Load a stub file from the filesystem
     *
     * @param string $type
     * @return string
     */
    protected function getStub(string $type) : string
    {
        return $this->files->get(__DIR__ . '/stubs/' . $type . '.stub');
    }
}

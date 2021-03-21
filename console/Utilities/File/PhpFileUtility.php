<?php

namespace DataContracts\Console\Utilities\File;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * @method static string addImportStatement(string $import)
 * @method static string removeImportStatement(string $import)
 * @method static string removeMethodDeclaration(string $name)
 * @method static string addNewProperty(string $visibility, string $name, mixed $value)
 * @method static string removeNewProperty(string $name)
 */
class PhpFileUtility
{
    const BLOCK_OPENING_TAG = 0;
    const BLOCK_NAMESPACE = 1;
    const BLOCK_IMPORTS = 2;

    /** @var array $blocks Block of code within file */
    protected $blocks = [];
    /** @var Filesystem $filesystem */
    protected $filesystem;

    /**
     * Mapping of static method calls to the underlying method they should
     * be forwarded to. Anything called this way will automatically,
     * be instantiated with the first arg as the file path, and ->get()
     * will be called automatically
     *
     * @var array $callStatic
     */
    protected static array $callStatic = [
        'addImportStatement' => 'addImport',
        'removeImportStatement' => 'removeImport',
        'removeMethodDeclaration' => 'removeMethod',
        'addPropertyDeclaration' => 'addProperty',
        'removePropertyDeclaration' => 'removeProperty',
    ];

    /**
     * PhpFileUtility Constructor
     *
     * @param string $file File contents (not just path)
     */
    public function __construct(string $file)
    {
        $this->blocks = explode(PHP_EOL . PHP_EOL, $file);
        $this->filesystem = new Filesystem;
    }

    /**
     * Redirect static method calls
     *
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $file = $arguments[0];
        $args = array_slice($arguments, 1);
        $instance = new static($file);

        $method = PhpFileUtility::$callStatic[$name];
        return $instance->$method(...$args)->get();
    }

    /**
     * Get the updated file contents
     *
     * @return string
     */
    public function get() : string
    {
        return implode(PHP_EOL . PHP_EOL, $this->blocks) . PHP_EOL;
    }

    /**
     * Get the file blocks that are used internally
     *
     * @return array
     */
    public function getBlocks() : array
    {
        return $this->blocks;
    }

    /**
     * Add an inport statement
     *
     * @param string $import
     * @param string|null $insertAfter
     * @return self
     */
    public function addImport(string $import, string $insertAfter = null) : self
    {
        $importStatement = "use $import;";
        $imports = explode(PHP_EOL, $this->blocks[self::BLOCK_IMPORTS]);
        $position = count($imports);

        if ($insertAfter !== null) {
            $statement = "use $insertAfter;";
            $position = array_search($statement, $imports);
        }

        // If not given a position, just add at end
        if ($insertAfter === null || $position === false) {
            $imports[] = "use $import;";
        }

        // If insertAfter is defined and a match is found,
        // build a new array with the new import at the desired index
        if ($insertAfter !== null && $position !== false) {
            $before  = array_slice($imports, 0, $position);
            $after  = array_slice($imports, $position + 1);
            $insert = [
                $insertAfter,
                $importStatement,
            ];
            $imports = [...$before, ...$insert, ...$after];
        }

        $this->blocks[self::BLOCK_IMPORTS] = implode(PHP_EOL, $imports);
        return $this;
    }

    /**
     * Remove an import statement
     *
     * @param string $import
     * @return self
     */
    public function removeImport(string $import) : self
    {
        $imports = explode(PHP_EOL, $this->blocks[self::BLOCK_IMPORTS]);
        $imports = array_filter($imports, function ($statement) use ($import) {
            $importStatement = "use $import;";
            return !Str::contains($statement, [$importStatement, $import]);
        });

        $this->blocks[self::BLOCK_IMPORTS] = implode(PHP_EOL, $imports);
        return $this;
    }

    /**
     * Remove a method call
     *
     * @param string $name
     * @return self
     */
    public function removeMethod(string $name) : self
    {
        // Use offset to skip directly to where methods are defined:
        // OPENING_TAG, NAMESPACE, IMPORTS, CLASS_DEFINITION
        $offset = self::BLOCK_IMPORTS + 1;
        $blocks = array_slice($this->blocks, $offset);
        $search = "function $name(";

        // Go through class definition, and filter out the specified method if
        // it appears
        $classBlocks = array_filter($blocks, function ($block) use ($search) {
            return !Str::contains($block, $search);
        });

        // Get the content of the blocks that we initially skipped:
        // OPENING_TAG, NAMESPACE, IMPORTS
        $topBlocks = array_slice($this->blocks, 0, $offset);
        // Combine blocks to get full file contents
        $this->blocks = [...$topBlocks, ...$classBlocks];
        return $this;
    }

    /**
     * Add a new property
     *
     * @param string $visibility
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function addProperty(string $visibility, string $name, $value = null) : self
    {
        // Use offset to skip directly to where methods are defined:
        // OPENING_TAG, NAMESPACE, IMPORTS, CLASS_DEFINITION
        $offset = self::BLOCK_IMPORTS + 1;
        $blocks = array_slice($this->blocks, $offset);

        // find first block containing a method
        $counter = 0;
        $position = array_reduce($blocks, function ($carry, $block) use (&$counter) {
            $hasMethod = (
                Str::contains($block, ['public', 'protected', 'private',]) &&
                Str::contains($block, ' function ')
            );

            if ($hasMethod && $counter !== false) {
                $carry = $counter;
            }

            $counter++;
            return $carry;
        }, false);

        // get type
        switch (gettype($value)) {
            case 'NULL':
                $type = null;
                break;
            case 'integer':
                $type = 'int';
                break;
            case 'double':
                $type = 'float';
                break;
            case 'boolean':
                $type = 'bool';
                $value = ($value === true) ? 'true' : 'false';
                break;
            default:
                $type = gettype($value);
                if ($type === 'array') {
                    $value = json_encode($value);
                }
        }

        // Make value printable
        if (is_object($value)) {
            $type = get_class($value);
            $value = null;
        }

        if (is_string($value)) {
            $value = "'$value'";
        }

        $prop = ($type === null) ? "$visibility $$name" : "$visibility $type $$name";
        $comment = ($type === null) ? "/** @var $$name */" : "/** @var $type $$name */";
        $newProp = ($value === null) ?
            "$prop;" : "$prop = $value;";

        $propertyBlock = [
            $blocks[$position - 1],
            '',
            "    $comment",
            "    $newProp",
        ];

        $blocks[$position - 1] = implode(PHP_EOL, $propertyBlock);
        // Get the content of the blocks that we initially skipped:
        // OPENING_TAG, NAMESPACE, IMPORTS
        $topBlocks = array_slice($this->blocks, 0, $offset);
        // Combine blocks to get full file contents
        $this->blocks = [...$topBlocks, ...$blocks];
        return $this;
    }

    /**
     * Remove a property
     *
     * @param string $name
     * @return self
     */
    public function removeProperty(string $name) : self
    {
        // Use offset to skip directly to where methods are defined:
        // OPENING_TAG, NAMESPACE, IMPORTS, CLASS_DEFINITION
        $offset = self::BLOCK_IMPORTS + 1;
        $blocks = array_slice($this->blocks, $offset);
        $property = "$$name";

        $blocks = array_filter($blocks, function ($block) use ($property) {
            if (
                Str::startsWith($block, 'class') ||
                Str::contains($block, ' function ')
            ) {
                return true;
            }

            $block = ltrim($block);

            return (
                Str::contains($block, ['public', 'protected', 'private',]) &&
                !Str::contains($block, $property)
            );
        });

        // Get the content of the blocks that we initially skipped:
        // OPENING_TAG, NAMESPACE, IMPORTS
        $topBlocks = array_slice($this->blocks, 0, $offset);
        // Combine blocks to get full file contents
        $this->blocks = [...$topBlocks, ...$blocks];

        return $this;
    }

    /**
     * Save the updated file to disk
     *
     * @param string $path
     * @return string
     */
    public function save(string $path) : string
    {
        $contents = $this->get();
        $this->filesystem->put($path, $contents);
        return $contents;
    }
}

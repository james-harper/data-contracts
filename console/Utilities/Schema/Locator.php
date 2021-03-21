<?php

namespace DataContracts\Console\Utilities\Schema;

use Illuminate\Support\Str;

/**
 * Schema validation data file locator
 */
class Locator
{
    /** @var string $schema Path to schema definition */
    protected string $schema;
    /** @var string $example Path to example implementation */
    protected string $example;
    /** @var string $type Type of entity being represented */
    protected string $type;

    const SCHEMA_DIRECTORY = __DIR__ . '/../../../data/';
    const EXAMPLE_DIRECTORY = __DIR__ . '/../../../data/examples/';

    /**
     * Initialise schema locations
     *
     * @param string $schemaType
     */
    public function __construct(string $schemaType)
    {
        $this->schema = self::SCHEMA_DIRECTORY . $schemaType;
        $this->example = self::EXAMPLE_DIRECTORY .$schemaType;
        $this->type = $schemaType;
    }

    /**
     * Load JSON Schema
     *
     * @return string
     */
    public function getSchema() : string
    {
        return file_get_contents($this->schema);
    }

    /**
     * Get path to JSON schema file
     *
     * @return string
     */
    public function getSchemaPath() : string
    {
        return $this->schema;
    }

    /**
     * Load Example implementation of contract
     *
     * @return object
     */
    public function getExample() : object
    {
        return json_decode(file_get_contents($this->example));
    }

    /**
     * Get path to example implementation
     *
     * @return void
     */
    public function getExamplePath() : string
    {
        return $this->example;
    }

    /**
     * Get the type of entity being represented with this contract
     *
     * @param bool $includeExtension
     * @return string
     */
    public function getDataType($includeExtension = true)
    {
        $type = $this->type;
        if (!$includeExtension) {
            return Str::before($type, '.');
        }

        return $type;
    }
}

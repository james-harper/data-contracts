<?php

namespace DataContracts\Console\Utilities\Schema;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator as JsonSchemaValidator;
use DataContracts\Console\Utilities\Formatting\ConsoleOutput;

/**
 * Validate whether data contract meets JSON Schema specification.
 * Requires Contract JSON as well as an example implementation
 */
class Validator
{
    /** @var ConsoleOutput $output */
    protected ConsoleOutput $output;

    /**
     * Validator constructor
     *
     * @param ConsoleOutput $output
     */
    public function __construct(ConsoleOutput $output = null)
    {
        $this->output = $output ?: new ConsoleOutput();
        $this->validator = new JsonSchemaValidator();
    }
    /**
     * Perform validation checks
     *
     * @param Locator $locator
     * @return bool Is Schema Valid?
     */
    public function run(Locator $locator) : bool
    {
        $type = $locator->getDataType();
        $schema = Schema::fromJsonString($locator->getSchema());
        $result = $this->validator->schemaValidation(
            $locator->getExample(),
            $schema
        );

        if ($result->isValid()) {
            $this->output->success(":white_checkmark: $type is a valid JSON Schema.");
            return true;
        }

        $error = $result->getFirstError();
        $this->output->danger(":construction: $type is an invalid JSON Schema.");

        $errorPointer = $error->dataPointer();
        // In some cases the error will be localised to a specific field,
        // In others, it will apply to the whole schema/example
        $errorField = count($errorPointer) ?
            $errorPointer[0] :
            $locator->getDataType(false);

        // Error data sometimes comes back as an object,
        // in those cases we can json_encode it to keep the handling consistent
        $errorData = $error->data();
        if (is_object($errorData)) {
            $errorData = json_encode($errorData);
        }

        $errorKeyword = $error->keyword();
        $errorInfo = $error->keywordArgs()[$errorKeyword] ?? null;
        if ($errorInfo !== null) {
            $errorInfo = "'$errorInfo'";
        }

        $this->output->warn('Error:');
        $this->output->warn("$errorKeyword $errorInfo", [], true);
        $this->output->warn('Received Value:');
        $this->output->warn("\"$errorData\"", [], true);
        $this->output->warn('Schema:');
        $this->output->warn(
            json_encode([$errorField => $error->keywordArgs()], JSON_PRETTY_PRINT),
            [],
            true
        );

        return false;
    }
}

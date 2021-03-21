<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Property;
use DataContracts\Schema\Constants\Type;

abstract class Rule
{
    /**
     * The property being tested against the rule
     *
     * @var Property
     */
    protected Property $property;

    /**
     * Any options that are relevant to the application of the rule.
     * Key should be a TYPE, and the value should be one or many options.
     * Some rules that might have different options depending on the data type.
     * Eg. `minLength` vs `minimum` for strings vs numbers
     *
     * @var array
     */
    protected $options = [];

    /**
     * Rule Constructor
     *
     * @param Property $property
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }

    /**
     * Get the name that Laravel uses for this rule
     * @return string
     */
    abstract public function getLaravelName() : string;

    /**
     * Helper function for generating rules that have an enum of acceptable values
     *
     * @param string $name The name of the rule
     * @param array $enum The list of acceptable values
     * @return string
     */
    protected function enum(string $name, array $enum) : string
    {
        return $name . LaravelRule::SEPARATOR . implode(',', $enum);
    }

    /**
     * Helper function for generating definitions for rules with a single value
     *
     * @param string $name
     * @param mixed $value
     * @return string
     */
    protected function ruleWithValue(string $name, $value = null)
    {
        $value =  $value ?? $this->property->getOption($this->getOptionForType());
        return $name . LaravelRule::SEPARATOR . $value;
    }

    /**
     * Get relevant options based on the current property's type
     *
     * @return mixed
     */
    protected function getOptionForType()
    {
        if (empty($this->options)) {
            return '';
        }

        $type = $this->property->getType();
        if (isset($this->options[$type])) {
            return $this->options[$type];
        }

        return $this->options[Type::ANY];
    }
}

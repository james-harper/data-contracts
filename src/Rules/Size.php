<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Type;

/**
 * Size validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-size
 */
class Size extends Rule
{
    /** @var MINIMUM_VALUE Minimum value */
    const MINIMUM_VALUE = 'minimum';
    /** @var MAXIMUM_VALUE maximum value */
    const MAXIMUM_VALUE = 'maximum';
    /** @var MINIMUM_ARRAY Minimum array value */
    const MINIMUM_ARRAY = 'minItems';
    /** @var MAXIMUM_ARRAY maximum array value */
    const MAXIMUM_ARRAY = 'maxItems';
    /** @var MINIMUM_STRING Minimum string value */
    const MINIMUM_STRING = 'minLength';
    /** @var MAXIMUM_STRING maximum string value */
    const MAXIMUM_STRING = 'maxLength';

    /**
     * Mapping of types to rules
     *
     * @var array
     */
    protected $options = [
        Type::STRING => [self::MINIMUM_STRING, self::MAXIMUM_STRING],
        Type::ARRAY => [self::MINIMUM_ARRAY, self::MAXIMUM_ARRAY],
        Type::ANY => [self::MINIMUM_VALUE, self::MAXIMUM_VALUE],
    ];

    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        $validTypes = [...Type::numeric(), Type::STRING, TYPE::ARRAY];
        if (!in_array($this->property->getType(), $validTypes)) {
            return false;
        }

        [$min, $max] = $this->getMinMax();
        return isset($min) && isset($max) && ($min === $max);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        [$min] = $this->getMinMax();
        return $this->ruleWithValue(LaravelRule::SIZE, $min);
    }

    /**
     * Convenience method for getting minimum and maximum values together
     *
     * @return array
     */
    protected function getMinMax() : array
    {
        [$min, $max] = $this->getOptionForType();
        return [
            $this->property->getOption($min),
            $this->property->getOption($max),
        ];
    }
}

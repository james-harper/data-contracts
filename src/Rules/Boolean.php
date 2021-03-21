<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Type;

/**
 * Boolean validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-boolean
 */
class Boolean extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        // Technically the specification also allows values that cast
        // to booleans, eg 1, 0, "1", "0"
        // but for now it's simpler to just do the casting ourselves if we
        // ever come across a situation where it is needed.
        return $this->property->isOfType(Type::BOOLEAN);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::BOOLEAN;
    }
}

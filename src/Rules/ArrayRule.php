<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Type;

/**
 * Array validation rule
 * (Ideally this would just be called Array - but it is a reserved word)
 *
 * @see https://laravel.com/docs/8.x/validation#rule-array
 */
class ArrayRule extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        return $this->property->isOfType(Type::ARRAY);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::ARRAY;
    }
}

<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Distinct validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-distinct
 */
class Distinct extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        if (!$this->property->isOfType(Type::ARRAY)) {
            return false;
        }

        $distinctRule = $this->property->getOption(Option::UNIQUE_ITEMS);
        return $distinctRule && $distinctRule === true;
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::DISTINCT;
    }
}

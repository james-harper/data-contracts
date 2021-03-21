<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Url validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-url
 */
class Url extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        if (!$this->property->isOfType(Type::STRING)) {
            return false;
        }

        $format = $this->property->getOption(Option::FORMAT);
        return $format && $format === Option::FORMAT_URI;
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::URL;
    }
}

<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Property;
use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Email validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-email
 */
class Email extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @param Property $property
     * @return boolean
     */
    public function check() : bool
    {
        if (!$this->property->isOfType(Type::STRING)) {
            return false;
        }

        $format = $this->property->getOption(Option::FORMAT);
        return $format && $format === LaravelRule::EMAIL;
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @param Property $property
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::EMAIL;
    }
}

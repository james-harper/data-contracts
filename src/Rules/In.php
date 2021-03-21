<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Property;
use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Modifier;

/**
 * In validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-in
 */
class In extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @param Property $property
     * @return boolean
     */
    public function check() : bool
    {
        $modifier = $this->property->getOption(Option::MODIFIER);
        if ($modifier && $modifier === Modifier::NOT) {
            return false;
        }

        return $this->property->hasOption(Option::ENUM);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @param Property $property
     * @return string
     */
    public function getLaravelName() : string
    {
        return $this->enum(LaravelRule::IN, $this->property->getOption(Option::ENUM));
    }
}

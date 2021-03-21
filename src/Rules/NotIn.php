<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Modifier;
use DataContracts\Schema\Constants\Option;

/**
 * NotIn validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-notin
 */
class NotIn extends Rule
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        $modifier = $this->property->getOption(Option::MODIFIER);
        return $modifier == Modifier::NOT && $this->property->hasOption(Option::ENUM);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return $this->enum(LaravelRule::NOT_IN, $this->property->getOption(Option::ENUM));
    }
}

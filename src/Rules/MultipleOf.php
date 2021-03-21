<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * MultipleOf validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-multipleof
 */
class MultipleOf extends Rule
{
    protected $options = [
        Type::ANY => Option::MULTIPLE_OF,
    ];

    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        if (!$this->property->isOfType(Type::NUMERIC)) {
            return false;
        }

        return $this->property->hasOption(Option::MULTIPLE_OF);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return $this->ruleWithValue(LaravelRule::MULTIPLE_OF);
    }
}

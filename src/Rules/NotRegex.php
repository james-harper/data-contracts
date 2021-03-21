<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Modifier;
use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * NotRegex validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-notregex
 */
class NotRegex extends Rule
{
    /**
     * Type to pattern mapping
     *
     * @var array
     */
    protected $options = [
        Type::STRING => Option::PATTERN,
    ];

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

        $modifier = $this->property->getOption(Option::MODIFIER);
        return $modifier === Modifier::NOT &&
            $this->property->hasOption(Option::PATTERN);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return $this->ruleWithValue(LaravelRule::NOT_REGEX);
    }
}

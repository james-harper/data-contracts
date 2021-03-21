<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Type;

/**
 * Numerc validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-date
 */
class Numeric extends Rule
{
    public function check() : bool
    {
        return $this->property->isOfType(Type::NUMERIC);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::NUMERIC;
    }
}

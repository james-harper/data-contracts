<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Property;
use DataContracts\Schema\Constants\Type;

/**
 * Max validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-max
 */
class Max extends Size
{
    /**
     * Checks if the rule should be applied
     *
     * @return bool
     */
    public function check() : bool
    {
        $validTypes = [...Type::numeric(), Type::STRING, Type::ARRAY];
        if (!in_array($this->property->getType(), $validTypes)) {
            return false;
        }

        [$min, $max] = $this->getMinMax();
        return isset($max) && $max !== $min;
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @param Property $property
     * @return string
     */
    public function getLaravelName() : string
    {
        [$min, $max] = $this->getMinMax();
        return $this->ruleWithValue(LaravelRule::MAX, $max);
    }
}

<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Type;

/**
 * Between validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-between
 */
class Between extends Size
{
    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        $validTypes = [...Type::numeric(), Type::STRING, TYPE::ARRAY];
        if (!in_array($this->property->getType(), $validTypes)) {
            return false;
        }

        [$min, $max] = $this->getMinMax();
        return isset($min) && isset($max) && $min !== $max;
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return $this->enum(LaravelRule::BETWEEN, $this->getMinMax());
    }
}

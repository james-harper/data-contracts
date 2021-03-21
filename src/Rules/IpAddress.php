<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * IP validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-ip
 */
class IpAddress extends Rule
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
        return in_array($format, [
            LaravelRule::IPV4,
            LaravelRule::IPV6,
        ]);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::IP;
    }
}

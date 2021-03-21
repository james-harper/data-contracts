<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Date Format validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-date_format
 */
class DateFormat extends Rule
{
    /**
     * Date formats and how they should be transformed into validation rules
     */
    protected array $dateFormats = [
        Option::FORMAT_FULLDATETIME => 'Y-m-d h:i:s',
        Option::FORMAT_FULLDATE => 'Y-m-d',
        Option::FORMAT_FULLTIME => 'h:i:s',
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

        $format = $this->property->getOption(Option::FORMAT);
        return in_array($format, array_keys($this->dateFormats));
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        $format = $this->property->getOption(Option::FORMAT);
        return $this->ruleWithValue(
            LaravelRule::DATE_FORMAT,
            $this->dateFormats[$format]
        );
    }
}

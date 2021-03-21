<?php

namespace DataContracts\Rules;

use Illuminate\Support\Collection;

class RulesRegistry
{
    /** @var array $rules All defined rules */
    private static array $rules = [
        Numeric::class,
        Date::class,
        In::class,
        Min::class,
        Max::class,
        Email::class,
        Between::class,
        Regex::class,
        IpAddress::class,
        MultipleOf::class,
        DateFormat::class,
        Url::class,
        Boolean::class,
        ArrayRule::class,
        Distinct::class,
        Image::class,
        NotIn::class,
        NotRegex::class,
        Size::class,
    ];

    /**
     * Get all rule definitions.
     * The returned array is sorted alphabetically so that the output
     * can be more easily predicted
     *
     * @return array
     */
    public static function all() : array
    {
        $rules = new Collection(static::$rules);
        return $rules->sortBy(function ($rule) {
            return (string) $rule;
        }, SORT_STRING)
        ->values()
        ->all();
    }
}

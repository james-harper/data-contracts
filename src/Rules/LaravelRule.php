<?php

namespace DataContracts\Rules;

/**
 * Constants for the names of Laravel validation rules
 * @see https://laravel.com/docs/8.x/validation
 */
interface LaravelRule
{
    const SEPARATOR = ':';
    const DATE = 'date';
    const EMAIL = 'email';
    const IN = 'in';
    const MAX = 'max';
    const MIN = 'min';
    const NUMERIC = 'numeric';
    const BETWEEN = 'between';
    const REGEX = 'regex';
    const IP = 'ip';
    const IPV4 = 'ipv4';
    const IPV6 = 'ipv6';
    const MULTIPLE_OF = 'multiple_of';
    const DATE_FORMAT = 'date_format';
    const URL = 'url';
    const BOOLEAN = 'boolean';
    const ALPHA = 'alpha';
    const ALPHA_DASH = 'alpha_dash';
    const ALPHA_NUM = 'alpha_num';
    const ARRAY = 'array';
    const DISTINCT = 'distinct';
    const IMAGE = 'image';
    const NOT_IN = 'not_in';
    const NOT_REGEX = 'not_regex';
    const SIZE = 'size';
    const STARTS_WITH = 'starts_with';
}

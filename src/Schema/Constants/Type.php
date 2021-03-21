<?php

namespace DataContracts\Schema\Constants;

/**
 * Data types used in JSON Schema definitions
 */
class Type
{
    /** @var STRING A string */
    const STRING = 'string';
    /** @var INTEGER An integer */
    const INTEGER = 'integer';
    /** @var NUMBER A number */
    const NUMBER = 'number';
    /** @var FLOAT A floating point number */
    const FLOAT = 'float';
    /** @var NUMERIC Any numeric value (includes ints, floats, etc) */
    const NUMERIC = 'numeric';
    /** @var BOOLEAN true or false (1 and 0 are not accepted in JSON Schema) */
    const BOOLEAN = 'boolean';
    /** @var NULL null is generally used to represent missing values */
    const NULL = 'null';
    /** @var ARRAY Arrays are used for ordered elements. In JSON, each element in an array may be of a different type. */
    const ARRAY = 'array';
    /**
     * @var ANY Any is essentially being used as a placeholder
     *  to define fallback behaviour when no other defined conditions have been
     * met. It is NOT analagous to Typescript's `any`
     **/
    const ANY = 'any';

    /**
     * Returns an array of all data types
     *
     * @return array
     */
    public static function all() : array
    {
        return array_merge([
            self::STRING,
            self::BOOLEAN,
            self::ARRAY,
        ], self::numeric());
    }

    /**
     * Returns an array of all numeric types
     * @return array
     */
    public static function numeric() : array
    {
        return [
            self::INTEGER,
            self::NUMBER,
            self::FLOAT,
        ];
    }
}

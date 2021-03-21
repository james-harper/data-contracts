<?php

namespace DataContracts\Tests\Constants;

/**
 * Test Group Constants
 */
final class Group
{
    const CACHE = 'cache';
    const CONTRACTS = 'contracts';
    const RULES = 'rules';
    const SCHEMAS = 'schemas';
    const VALIDATION = 'validation';

    /**
     * Get all test groups
     *
     * @return array
     */
    public static function all() : array
    {
        return [
            self::CACHE,
            self::CONTRACTS,
            self::RULES,
            self::SCHEMAS,
            self::VALIDATION,
        ];
    }
}

<?php

namespace DataContracts\Cache;

/**
 * Constants related to caching values
 */
interface Constants
{
    const SEPARATOR = ':';
    const ALL = 'all';
    // Describe calls use the same cache as all
    // (because it is simple enough to filter out identifiers on request)
    const DESCRIBE = 'all';
    const RULES = 'rules';
    const CONTRACTS = 'contracts';
}

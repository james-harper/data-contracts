<?php

namespace DataContracts\Schema\Constants;

/**
 * Constants for JSON Schema Modifiers/Schema combiners
 */
interface Modifier
{
    const ALL_OF = 'allOf';
    const ANY_OF = 'anyOf';
    const ONE_OF = 'oneOf';
    const NOT = 'not';
}

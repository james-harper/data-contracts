<?php

namespace DataContracts\Schema\Constants;

/**
 * Constants for JSON Schema Options
 */
interface Option
{
    const TYPE = 'type';
    const FORMAT = 'format';
    const ENUM = 'enum';
    const PATTERN = 'pattern';
    const MULTIPLE_OF = 'multipleOf';
    const UNIQUE_ITEMS = 'uniqueItems';
    const FORMAT_DATE = 'date';
    const FORMAT_DATETIME = 'date-time';
    const FORMAT_FULLDATE = 'full-date';
    const FORMAT_FULLTIME = 'full-time';
    const FORMAT_FULLDATETIME = 'full-date full-time';
    const FORMAT_URI = 'uri';
    const BASE_64 = 'base64';
    const CONTENT_ENCODING = 'contentEncoding';
    const CONTENT_MEDIA_TYPE = 'contentMediaType';
    const MODIFIER = 'modifier';
}

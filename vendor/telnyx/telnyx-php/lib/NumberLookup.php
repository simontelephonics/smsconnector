<?php

namespace Telnyx;

/**
 * Class NumberLookup
 *
 * @package Telnyx
 */
class NumberLookup extends ApiResource
{
    const OBJECT_NAME = "number_lookup";

    use ApiOperations\Retrieve;
    
    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        // NOTE: This function override compensates for the lack of an S at the end of this endpoint.
        // Original function inside ApiResource.php
        $base = str_replace('.', '/', static::OBJECT_NAME);
        return "/v2/${base}";
    }
}

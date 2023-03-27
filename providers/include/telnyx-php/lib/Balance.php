<?php

namespace Telnyx;

/**
 * Class Balance
 *
 * @package Telnyx
 */
class Balance extends ApiResource
{
    const OBJECT_NAME = "balance";

    use ApiOperations\Retrieve;
    
    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        // NOTE: This function override compensates for the lack of an S at the end of this endpoint.
        // Original function inside ApiResource.php
        return "/v2/balance";
    }

    /**
     * @param string|null $id
     *
     * @return Retrieve user balance details
     */
    public static function retrieve()
    {
        list($response, $opts) = static::_staticRequest('get', static::classUrl(), null, null);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }
}

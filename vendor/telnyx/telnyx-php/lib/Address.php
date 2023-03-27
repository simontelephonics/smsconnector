<?php

namespace Telnyx;

/**
 * Class Address
 *
 * @package Telnyx
 */
class Address extends ApiResource
{
    const OBJECT_NAME = "address";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    
    /**
     * @return string The endpoint associated with this singleton class.
     */
    public static function classUrl()
    {
        // Use a custom URL for this resource
        // NOTE: This endpoint is special because object name is "address" and endpoint is "addresses"
        return "/v2/addresses";
    }
}

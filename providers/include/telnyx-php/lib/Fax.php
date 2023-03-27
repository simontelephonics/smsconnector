<?php

namespace Telnyx;

/**
 * Class Fax
 *
 * @package Telnyx
 */
class Fax extends ApiResource
{
    const OBJECT_NAME = "fax"; // The record_type is 'fax' and the endpoint is /faxes which is changed in classUrl() below

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;

    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        // NOTE: This function override compensates for the different way this endpoint is spelled.
        // 'faxs' vs 'faxes'
        // Original function inside ApiResource.php
        return "/v2/faxes";
    }
}

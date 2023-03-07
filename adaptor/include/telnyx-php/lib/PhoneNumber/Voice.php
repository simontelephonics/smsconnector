<?php

namespace Telnyx\PhoneNumber;

/**
 * Class Voice
 *
 * @package Telnyx
 */
class Voice extends \Telnyx\ApiResource
{
    const OBJECT_NAME = "voice_settings";

    use \Telnyx\ApiOperations\All;
    
    /**
     * @return string The endpoint URL for the given class.
     */
    public static function classUrl()
    {
        // NOTE: This comes from ApiResource.php, but we override this because it is an endpoint with a singular ending.
        return "/v2/phone_numbers/voice";
    }
}

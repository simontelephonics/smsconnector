<?php

namespace Telnyx;

/**
 * Class Verification
 *
 * @package Telnyx
 */
class Verification extends ApiResource
{
    const OBJECT_NAME = "verification";

    use ApiOperations\Create;
    use ApiOperations\Retrieve;

    /**
     * Retrieve a verification by phone number
     *
     * @param string $phone_number
     * @param array|string|null $options
     *
     * @return \Telnyx\TwoFactorVerify
     */
    public static function retrieve_by_phone_number($phone_number, $options = null)
    {
        $url = '/v2/verifications/by_phone_number/' . urlencode($phone_number);

        list($response, $opts) = static::_staticRequest('get', $url, null, $options);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }

    /**
     * Submit a verification code
     *
     * @param string $phone_number
     * @param string $verification_code
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public static function submit_verification($phone_number, $verification_code, $options = null)
    {
        $params = ['code' => $verification_code];
        self::_validateParams($params);
        $url = '/v2/verifications/by_phone_number/' . urlencode($phone_number) . '/actions/verify';

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }
}
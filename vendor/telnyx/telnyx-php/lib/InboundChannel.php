<?php

namespace Telnyx;

/**
 * Class InboundChannel
 *
 * @package Telnyx
 */
class InboundChannel extends ApiResource
{
    const OBJECT_NAME = "inbound_channels";
    const OBJECT_URL = "/v2/phone_numbers/inbound_channels";

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return Returns the inbound channels for your account.
     */
    public static function retrieve()
    {
        $url = self::OBJECT_URL;

        list($response, $opts) = static::_staticRequest('get', $url, null, null);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return Update the inbound channels for the account
     */
    public static function update($params = null)
    {
        self::_validateParams($params);
        $url = self::OBJECT_URL;

        list($response, $opts) = static::_staticRequest('patch', $url, $params, null);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }

}

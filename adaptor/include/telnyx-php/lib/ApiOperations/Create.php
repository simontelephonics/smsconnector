<?php

namespace Telnyx\ApiOperations;

/**
 * Trait for creatable resources. Adds a `create()` static method to the class.
 *
 * This trait should only be applied to classes that derive from TelnyxObject.
 */
trait Create
{
    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\ApiResource The created resource.
     */
    public static function create($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }
}

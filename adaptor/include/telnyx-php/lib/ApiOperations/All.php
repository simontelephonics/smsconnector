<?php

namespace Telnyx\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from TelnyxObject.
 */
trait All
{
    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return \Telnyx\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);

        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);

        // This is needed for nextPage() and previousPage()
        $response->json['url'] = $url;

        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        if (!is_a($obj, \Telnyx\Collection::class)) {
            throw new \Telnyx\Exception\UnexpectedValueException(
                'Expected type ' . \Telnyx\Collection::class . ', got "' . \get_class($obj) . '" instead.'
            );
        }
        $obj->setLastResponse($response);
        $obj->setRequestParams($params);
        
        return $obj;
    }
}

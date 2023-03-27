<?php

namespace Telnyx\ApiOperations;

/**
 * Trait for deletable resources. Adds a `delete()` method to the class.
 *
 * This trait should only be applied to classes that derive from TelnyxObject.
 */
trait Delete
{
    /**
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return \Telnyx\ApiResource The deleted resource.
     */
    public function delete($params = null, $opts = null)
    {
        self::_validateParams($params);

        $url = $this->instanceUrl();
        list($response, $opts) = $this->_request('delete', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }
    
    /**
     * @param string $id The ID of the resource to delete.
     * @param array|null $params
     * @param array|string|null $opts
     *
     * @return \Telnyx\ApiResource The updated resource.
     */
    public static function remove($id, $params = null, $opts = null)
    {
        self::_validateParams($params);

        $url = static::resourceUrl($id);

        list($response, $opts) = static::_staticRequest('delete', $url, $params, $opts);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }
}

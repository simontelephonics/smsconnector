<?php

namespace Telnyx;

/**
 * Class SimCard
 *
 * @package Telnyx
 */
class SimCard extends ApiResource
{

    const OBJECT_NAME = "sim_card";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    /**
     * Request a SIM card activation (DEPRECATED)
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function activate($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/activate';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Request a SIM card deactivation (DEPRECATED)
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function deactivate($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/deactivate';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Request a SIM card enable
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function enable($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/enable';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Request a SIM card disable
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function disable($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/disable';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Register the SIM cards associated with the provided registration codes to
     * the current user's account.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public static function register($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = '/v2/actions/register/sim_cards';

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }

    /**
     * Deletes the network preferences currently applied in the SIM card.
     *
     * @param string|null $id
     *
     * @return
     */
    public static function delete_network_preferences($id)
    {
        $url = '/v2/sim_cards/' . $id . '/network_preferences';

        list($response, $opts) = static::_staticRequest('delete', $url, null, null);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }

    /**
     * Sets the network preferences currently applied in the SIM card.
     *
     * @param string|null $id
     *
     * @return
     */
    public static function get_network_preferences($id)
    {
        $url = '/v2/sim_cards/' . $id . '/network_preferences';

        list($response, $opts) = static::_staticRequest('get', $url, null, null);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }

    /**
     * Returns the network preferences currently applied in the SIM card.
     *
     * @param string|null $id
     *
     * @return
     */
    public static function set_network_preferences($id, $params = null, $options = null)
    {
        $url = '/v2/sim_cards/' . $id . '/network_preferences';

        list($response, $opts) = static::_staticRequest('put', $url, $params, $options);
        $obj = \Telnyx\Util\Util::convertToTelnyxObject($response->json, $opts);
        return $obj;
    }
}

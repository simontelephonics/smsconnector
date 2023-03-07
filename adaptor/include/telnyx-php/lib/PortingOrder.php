<?php

namespace Telnyx;

/**
 * Class PortingOrder
 *
 * @package Telnyx
 */
class PortingOrder extends ApiResource
{
    const OBJECT_NAME = "porting_order";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    use ApiOperations\Delete;


    /**
     * Download a porting order loa template
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function loa_template($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/loa_template';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Confirms the porting order is ready to be actioned
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function confirm($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/confirm';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}

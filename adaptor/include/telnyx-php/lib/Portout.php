<?php

namespace Telnyx;

/**
 * Class Portout
 *
 * @package Telnyx
 */
class Portout extends ApiResource
{

    const OBJECT_NAME = "portout";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

    /**
     * Authorize or reject portout request
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function update_status($status)
    {
        $url = $this->instanceUrl() . '/' . $status;
        list($response, $opts) = $this->_request('patch', $url, null, null);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * List all comments for a portout request
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function list_comments()
    {
        $url = $this->instanceUrl() . '/comments';
        list($response, $opts) = $this->_request('get', $url, null, null);

        // This is needed for nextPage() and previousPage()
        $response['url'] = $url;

        $obj = Util\Util::convertToTelnyxObject($response, $opts);
        return $obj;
    }

    /**
     * Create a comment on a portout request
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function create_comment($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/comments';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

}

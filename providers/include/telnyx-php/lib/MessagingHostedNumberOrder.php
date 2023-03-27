<?php

namespace Telnyx;

/**
 * Class MessagingHostedNumberOrder
 *
 * @package Telnyx
 */
class MessagingHostedNumberOrder extends ApiResource
{
    const OBJECT_NAME = "messaging_hosted_number_order";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Delete;


    /**
     * Upload file required for a messaging hosted number order
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function file_upload($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/file_upload';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

}

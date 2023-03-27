<?php

namespace Telnyx;

/**
 * Class MessagingProfile
 *
 * @package Telnyx
 */
class MessagingProfile extends ApiResource
{
    const OBJECT_NAME = "messaging_profile";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;


    /**
     * List all phone numbers associated with a messaging profile.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function phone_numbers($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/phone_numbers';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * List all short codes associated with a messaging profile.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function short_codes($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/short_codes';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * List all sender IDs associated with a messaging profile.
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function alphanumeric_sender_ids($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/alphanumeric_sender_ids';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}

<?php

namespace Telnyx;

/**
 * Class Conference
 *
 * @package Telnyx
 */
class Conference extends ApiResource
{
    const OBJECT_NAME = "conference";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;


    /**
     * Join an existing call leg to a conference. Issue the Join Conference command
     * with the conference ID in the path and the call_control_id of the leg you 
     * wish to join to the conference as an attribute.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function join($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/join';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Mute a list of participants in a conference call
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function mute($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/mute';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Unmute a list of participants in a conference call
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function unmute($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/unmute';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Hold a list of participants in a conference call
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function hold($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/hold';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Unhold a list of participants in a conference call
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return 
     */
    public function unhold($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/unhold';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * List conference participants
     * Convert text to speech and play it to all or some participants.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\Collection
     */
    public function participants($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/participants';
        list($response, $opts) = $this->_request('get', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Dial a new participant into a conference
     * Convert text to speech and play it to all or some participants.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public function dial_participant($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/dial_participant';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Speak text to conference participants
     * Convert text to speech and play it to all or some participants.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public function speak($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/speak';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Play audio to conference participants
     * Play audio to all or some participants on a conference call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public function play($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/play';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Conference recording start
     * Start recording the conference. Recording will stop on conference end, or via the Stop Recording command.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public function record_start($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_start';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Conference recording stop
     * Stop recording the conference.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return \Telnyx\TelnyxObject
     */
    public function record_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}

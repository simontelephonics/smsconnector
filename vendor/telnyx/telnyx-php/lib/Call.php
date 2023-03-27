<?php

namespace Telnyx;

/**
 * Class Call
 *
 * @package Telnyx
 */
class Call extends ApiResource
{
    const OBJECT_NAME = "call";
    const OBJECT_ID = "call_control_id";

    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\NestedResource; // NOTE: This might be unused.

    /**
     * Answer an incoming call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function answer($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/answer';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Bridge two call control calls.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function bridge($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/bridge';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return Forking start
     */
    public function fork_start($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/fork_start';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Stop forking a call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function fork_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/fork_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Play an audio file on the call until the required DTMF signals are
     * gathered to build interactive menus.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function gather_using_audio($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/gather_using_audio';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Convert text to speech and play it on the call until the required DTMF
     * signals are gathered to build interactive menus.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function gather_using_speak($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/gather_using_speak';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Hang up the call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function hangup($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/hangup';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Play an audio file on the call. If multiple play audio commands are
     * issued consecutively, the audio files will be placed in a queue awaiting
     * playback.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function playback_start($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/playback_start';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Stop audio being played on the call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function playback_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/playback_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Start recording the call. Recording will stop on call hang-up, or can be
     * initiated via the Stop Recording command.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function record_start($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_start';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Stop recording the call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function record_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Reject an incoming call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function reject($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/reject';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Sends DTMF tones from this leg. DTMF tones will be heard by the other
     * end of the call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function send_dtmf($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/send_dtmf';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Convert text to speech and play it back on the call. If multiple speak
     * text commands are issued consecutively, the audio files will be placed
     * in a queue awaiting playback.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function speak($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/speak';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Transfer a call to a new destination. If the transfer is unsuccessful,
     * a call.hangup webhook will be sent indicating that the transfer could
     * not be completed. The original call will remain active and may be issued
     * additional commands, potentially transfering the call to an alternate
     * destination.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function transfer($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/transfer';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Start real-time transcription. Transcription will stop on call hang-up,
     * or can be initiated via the Transcription stop command.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function transcription_start($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/transcription_start';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Stop real-time transcription.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function transcription_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/transcription_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Pause recording the call. Recording can be resumed via Resume recording command.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function record_pause($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_pause';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Resume recording the call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function record_resume($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/record_resume';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Stop current gather.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function gather_stop($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/gather_stop';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * SIP Refer a call
     *
     * Initiate a SIP Refer on a Call Control call. You can initiate a SIP Refer
     * at any point in the duration of a call.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function refer($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/refer';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Enqueue call
     *
     * Put the call in a queue.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function enqueue($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/enqueue';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    /**
     * Remove call from a queue
     *
     * Removes the call from a queue.
     *
     * @param array|null $params
     * @param array|string|null $options
     *
     * @return
     */
    public function leave_queue($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/actions/leave_queue';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

}

<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Call
 */
final class CallTest extends \Telnyx\TestCase
{
    const CALL_CONTROL_ID = 'v2:T02llQxIyaRkhfRKxgAP8nY511EhFLizdvdUKJiSw8d6A9BborherQ';

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/calls'
        );
        $resource = Call::create([
            'connection_id' => 'uuid',
            'to' => '+18005550199',
            'from' => '+18005550100'
        ]);
        $this->assertInstanceOf(\Telnyx\Call::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID)
        );
        $resource = Call::retrieve(self::CALL_CONTROL_ID);
        $this->assertInstanceOf(\Telnyx\Call::class, $resource);
    }

    public function testAnswer()
    {
        $call = Call::retrieve(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/answer'
        );
        $resource = $call->answer([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testBridge()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/bridge'
        );
        $resource = $call->bridge([
            'call_control_id' => self::CALL_CONTROL_ID
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testForkStart()
    {
        $call = Call::retrieve(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/fork_start'
        );
        $resource = $call->fork_start([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testForkStop()
    {
        $call = Call::retrieve(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/fork_stop'
        );
        $resource = $call->fork_stop([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testGatherUsingAudio()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/gather_using_audio'
        );
        $resource = $call->gather_using_audio([
            'audio_url' => 'http://example.com/message.wav'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testGatherUsingSpeak()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/gather_using_speak'
        );
        $resource = $call->gather_using_speak([
            'language' => 'en-US',
            'voice' => 'female',
            'payload' => 'Telnyx call control test'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testHangup()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/hangup'
        );
        $resource = $call->hangup([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testPlaybackStart()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/playback_start'
        );
        $resource = $call->playback_start([
            'audio_url' => 'http://www.example.com/sounds/greeting.wav'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testPlaybackStop()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/playback_stop'
        );
        $resource = $call->playback_stop([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testRecordStart()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/record_start'
        );
        $resource = $call->record_start([
            'channels' => 'single',
            'format' => 'mp3'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testRecordStop()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/record_stop'
        );
        $resource = $call->record_stop([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testReject()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/reject'
        );
        $resource = $call->reject([
            'cause' => 'USER_BUSY'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testSendDTMF()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/send_dtmf'
        );
        $resource = $call->send_dtmf(['digits' => '1www2WABCDw9']);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testSpeak()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/speak'
        );
        $resource = $call->speak([
            'digits' => '1www2WABCDw9',
            'language' => 'en-US',
            'voice' => 'female',
            'payload' => 'Say this on the call'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testTranscriptionStart()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/transcription_start'
        );
        $resource = $call->transcription_start([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testTranscriptionStop()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/transcription_stop'
        );
        $resource = $call->transcription_stop([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testRecordPause()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/record_pause'
        );
        $resource = $call->record_pause([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testRecordResume()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/record_resume'
        );
        $resource = $call->record_resume([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testGatherStop()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/gather_stop'
        );
        $resource = $call->gather_stop([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testRefer()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/refer'
        );
        $resource = $call->refer([
            'sip_address' => 'sip:username@sip.non-telnyx-address.com'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testEnqueue()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/enqueue'
        );
        $resource = $call->enqueue([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d',
            'command_id' => '891510ac-f3e4-11e8-af5b-de00688a4901'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testLeaveQueue()
    {
        $call = new Call(self::CALL_CONTROL_ID);

        $this->expectsRequest(
            'post',
            '/v2/calls/' . urlencode(self::CALL_CONTROL_ID) . '/actions/leave_queue'
        );
        $resource = $call->leave_queue([
            'client_state' => 'aGF2ZSBhIG5pY2UgZGF5ID1d',
            'command_id' => '891510ac-f3e4-11e8-af5b-de00688a4901'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
}

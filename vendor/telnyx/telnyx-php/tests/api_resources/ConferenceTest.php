<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Conference
 */
final class ConferenceTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';
    const TEST_CALL_CONTROL_ID = '891510ac-f3e4-11e8-af5b-de00688a4931';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/conferences'
        );
        $resources = Conference::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\Conference::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/conferences'
        );
        $resource = Conference::create([
            "name" => "Business",
            "call_control_id" => self::TEST_CALL_CONTROL_ID
        ]);
        $this->assertInstanceOf(\Telnyx\Conference::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Conference::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Conference::class, $resource);
    }

    public function testJoin()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/join'
        );
        $resource = $conference->join([
            'call_control_id' => self::TEST_CALL_CONTROL_ID
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    
    public function testMute()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/mute'
        );
        $resource = $conference->mute([
            'call_control_ids' => [self::TEST_CALL_CONTROL_ID]
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    
    public function testUnute()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/unmute'
        );
        $resource = $conference->unmute([
            'call_control_ids' => [self::TEST_CALL_CONTROL_ID]
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    
    public function testHold()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/hold'
        );
        $resource = $conference->hold([
            'audio_url' => 'http://example.com/message.wav',
            'call_control_ids' => [self::TEST_CALL_CONTROL_ID]
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    
    public function testUnhold()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/unhold'
        );
        $resource = $conference->unhold([
            'call_control_ids' => [self::TEST_CALL_CONTROL_ID]
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    
    public function testParticipants()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'get',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/participants'
        );
        $resources = $conference->participants();
        $this->assertInstanceOf(\Telnyx\Conference::class, $resources);
    }

    /*
    public function testDialParticipant()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/dial_participant'
        );
        $resource = $conference->dial_participant();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
    */
    
    public function testSpeak()
    {
        $conference = Conference::retrieve(self::TEST_RESOURCE_ID);

        $this->expectsRequest(
            'post',
            '/v2/conferences/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/speak'
        );
        $resource = $conference->speak([
            'language' => 'en-US',
            'payload' => 'Say this to participants',
            'voice' => 'female'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

}

<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\OutboundVoiceProfile
 */
final class OutboundVoiceProfileTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/outbound_voice_profiles'
        );
        $resources = OutboundVoiceProfile::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\OutboundVoiceProfile::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/outbound_voice_profiles'
        );
        $resource = OutboundVoiceProfile::create(["name" => "office"]);
        $this->assertInstanceOf(\Telnyx\OutboundVoiceProfile::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = OutboundVoiceProfile::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/outbound_voice_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\OutboundVoiceProfile::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/outbound_voice_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = OutboundVoiceProfile::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\OutboundVoiceProfile::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/outbound_voice_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = OutboundVoiceProfile::update(self::TEST_RESOURCE_ID, [
            "name" => "office"
        ]);
        $this->assertInstanceOf(\Telnyx\OutboundVoiceProfile::class, $resource);
    }
}

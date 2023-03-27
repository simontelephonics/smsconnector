<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\PhoneNumber
 */
final class PhoneNumberTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers'
        );
        $resources = PhoneNumber::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PhoneNumber::class, $resources['data'][0]);
    }

    public function testIsDeletable()
    {
        $resource = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\PhoneNumber::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\PhoneNumber::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = PhoneNumber::update(self::TEST_RESOURCE_ID, [
            "name" => "Test",
        ]);
        $this->assertInstanceOf(\Telnyx\PhoneNumber::class, $resource);
    }

    public function testVoice()
    {
        $phone_number = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID) . '/voice'
        );
        $resource = $phone_number->voice();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource); // record_type: voice_settings
    }

    public function testUpdateVoice()
    {
        $phone_number = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'patch',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID) . '/voice'
        );
        $resource = $phone_number->update_voice();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource); // record_type: voice_settings
    }

    public function testMessaging()
    {
        $phone_number = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID) . '/messaging'
        );
        $resource = $phone_number->messaging();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource); // record_type: voice_settings
    }

    public function testUpdateMessaging()
    {
        $phone_number = PhoneNumber::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'patch',
            '/v2/phone_numbers/' . urlencode(self::TEST_RESOURCE_ID) . '/messaging'
        );
        $resource = $phone_number->update_messaging();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource); // record_type: voice_settings
    }
}

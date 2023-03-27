<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\TelephonyCredential
 */
final class TelephonyCredentialTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = 'c215ade3-0d39-418e-94be-c5f780760199';
    
    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/telephony_credentials'
        );
        $resources = TelephonyCredential::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\TelephonyCredential::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/telephony_credentials'
        );
        $resource = TelephonyCredential::create(["connection_id" => "1234567890", "name" => "My-new-credential"]);
        $this->assertInstanceOf(\Telnyx\TelephonyCredential::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/telephony_credentials/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = TelephonyCredential::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelephonyCredential::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/telephony_credentials/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = TelephonyCredential::update(self::TEST_RESOURCE_ID, ["connection_id" => "987654321", "name" => "My-new-updated-credential"]);
        $this->assertInstanceOf(\Telnyx\TelephonyCredential::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = TelephonyCredential::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/telephony_credentials/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\TelephonyCredential::class, $resource);
    }

}

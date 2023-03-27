<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\IP
 */
final class IPTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ips'
        );
        $resources = IP::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\IP::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/ips'
        );
        $resource = IP::create(["connection_id" => "UUID", "ip_address" => "192.168.0.0"]);
        $this->assertInstanceOf(\Telnyx\IP::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = IP::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/ips/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\IP::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ips/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = IP::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\IP::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/ips/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = IP::update(self::TEST_RESOURCE_ID, [
            "connection_id" => "Central BSD-1",
            "ip_address" => "192.168.0.0"
        ]);
        $this->assertInstanceOf(\Telnyx\IP::class, $resource);
    }
}

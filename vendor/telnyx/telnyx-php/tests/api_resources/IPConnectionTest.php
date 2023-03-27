<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\IPConnection
 */
final class IPConnectionTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ip_connections'
        );
        $resources = IPConnection::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/ip_connections'
        );
        $resource = IPConnection::create(["connection_name" => "My connection name"]);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = IPConnection::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/ip_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ip_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = IPConnection::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/ip_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = IPConnection::update(self::TEST_RESOURCE_ID, [
            "connection_name" => "Central BSD-1"
        ]);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resource);
    }
}

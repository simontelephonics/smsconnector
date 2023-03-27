<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Connection
 */
final class ConnectionTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/connections'
        );
        $resources = Connection::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Connection::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Connection::class, $resource);
    }
}

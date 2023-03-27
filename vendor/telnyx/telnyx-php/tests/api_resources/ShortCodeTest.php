<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ShortCode
 */
final class ShortCodeTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/short_codes'
        );
        $resources = ShortCode::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\ShortCode::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/short_codes/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = ShortCode::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\ShortCode::class, $resource);
    }
}

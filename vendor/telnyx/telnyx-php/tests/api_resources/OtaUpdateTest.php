<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\OtaUpdate
 */
final class OtaUpdateTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '6a09cdc3-8948-47f0-aa62-74ac943d6c58';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ota_updates'
        );
        $resources = OtaUpdate::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\OtaUpdate::class, $resources['data'][0]);
    }
    
    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/ota_updates/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = OtaUpdate::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\OtaUpdate::class, $resource);
    }
}

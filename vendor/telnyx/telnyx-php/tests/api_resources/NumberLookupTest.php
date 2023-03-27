<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\NumberLookup
 */
final class NumberLookupTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_lookup/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = NumberLookup::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\NumberLookup::class, $resource);
    }
}

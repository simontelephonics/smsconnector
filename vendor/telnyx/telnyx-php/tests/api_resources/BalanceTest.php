<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Balance
 */
final class BalanceTest extends \Telnyx\TestCase
{
    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/balance'
        );
        $resource = Balance::retrieve();
        $this->assertInstanceOf(\Telnyx\Balance::class, $resource);
    }
}

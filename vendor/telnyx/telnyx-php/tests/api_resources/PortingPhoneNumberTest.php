<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\PortingPhoneNumber
 */
final class PortingPhoneNumberTest extends \Telnyx\TestCase
{
    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/porting_phone_numbers'
        );
        $resources = PortingPhoneNumber::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PortingPhoneNumber::class, $resources['data'][0]);
    }

}

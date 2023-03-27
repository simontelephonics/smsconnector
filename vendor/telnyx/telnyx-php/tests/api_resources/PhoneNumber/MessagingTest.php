<?php

namespace Telnyx\PhoneNumber;

/**
 * @internal
 * @covers \Telnyx\PhoneNumber\Messaging
 */
final class MessagingTest extends \Telnyx\TestCase
{

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers/messaging'
        );
        $resources = Messaging::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PhoneNumber\Messaging::class, $resources['data'][0]);
    }

}

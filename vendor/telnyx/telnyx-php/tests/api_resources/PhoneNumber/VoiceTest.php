<?php

namespace Telnyx\PhoneNumber;

/**
 * @internal
 * @covers \Telnyx\PhoneNumber\Voice
 */
final class VoiceTest extends \Telnyx\TestCase
{

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/phone_numbers/voice'
        );
        $resources = Voice::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PhoneNumber\Voice::class, $resources['data'][0]);
    }

}

<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\AvailablePhoneNumber
 */
final class TestAvailablePhoneNumber extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '+18005554000';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/available_phone_numbers'
        );
        $resources = AvailablePhoneNumber::all([
            'filter' => [
                "phone_number" => ["starts_with" => "555"],
            ]
        ]);
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\AvailablePhoneNumber::class, $resources['data'][0]);
    }
}

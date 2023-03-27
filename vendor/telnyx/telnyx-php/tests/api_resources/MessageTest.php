<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Message
 */
final class MessageTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '3fa85f64-5717-4562-b3fc-2c963f66afa6';

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/messages'
        );

        $resource = \Telnyx\Message::Create([
            "from" => "+13125550100",
            "to" => "+17735550100",
            "text" => "Hello!"
        ]);

        $this->assertInstanceOf(\Telnyx\Message::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/messages/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = \Telnyx\Message::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Message::class, $resource);
    }
}

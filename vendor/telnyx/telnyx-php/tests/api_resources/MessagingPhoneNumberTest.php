<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\MessagingPhoneNumber
 * @deprecated Tests are expected to fail even though this endpoint currently is working in production.
 */
final class MessagingPhoneNumberTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '+18005554000';

    public function testIsListable()
    {
        $this->expectException('Telnyx\Exception\UnexpectedValueException');
        
        $this->expectsRequest(
            'get',
            '/v2/messaging_phone_numbers'
        );
        $resources = MessagingPhoneNumber::all();
    }

    public function testIsRetrievable()
    {
        $this->expectException('Telnyx\Exception\UnexpectedValueException');

        $this->expectsRequest(
            'get',
            '/v2/messaging_phone_numbers/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = MessagingPhoneNumber::retrieve(self::TEST_RESOURCE_ID);
    }

    public function testIsUpdatable()
    {
        $this->expectException('Telnyx\Exception\UnexpectedValueException');

        $this->expectsRequest(
            'patch',
            '/v2/messaging_phone_numbers/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = MessagingPhoneNumber::update(self::TEST_RESOURCE_ID, [
            "name" => "value",
        ]);
    }
}

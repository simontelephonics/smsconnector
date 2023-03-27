<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\Create
 */

class DummyCreate extends ApiResource
{
    const OBJECT_NAME = 'ip_connection';

    use \Telnyx\ApiOperations\Create;
}

final class CreateTest extends \Telnyx\TestCase
{
    public function testTraitCreate()
    {
        $result = DummyCreate::create(['connection_name'=>'connection name']);
        $this->assertInstanceOf(\Telnyx\IPConnection::class, $result);
    }
}

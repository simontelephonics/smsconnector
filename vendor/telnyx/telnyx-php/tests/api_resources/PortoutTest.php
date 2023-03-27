<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Portout
 */
final class PortoutTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/portouts'
        );
        $resources = Portout::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\Portout::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/portouts/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Portout::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Portout::class, $resource);
    }

    public function testUpdateStatus()
    {
        $portout_status = 'authorized';
        $portout = Portout::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'patch',
            '/v2/portouts/' . urlencode(self::TEST_RESOURCE_ID) . '/' . $portout_status
        );
        $resources = $portout->update_status($portout_status);
        $this->assertInstanceOf(\Telnyx\Portout::class, $resources);
    }

    public function testListComments()
    {
        $portout = Portout::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'get',
            '/v2/portouts/' . urlencode(self::TEST_RESOURCE_ID) . '/comments'
        );
        $resources = $portout->list_comments();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\Portout::class, $resources['data'][0]);
    }

    public function testCreateComment()
    {
        $portout = Portout::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/portouts/' . urlencode(self::TEST_RESOURCE_ID) . '/comments'
        );
        $resources = $portout->create_comment(['body'=>'comment']);
        $this->assertInstanceOf(\Telnyx\Portout::class, $resources);
    }
}

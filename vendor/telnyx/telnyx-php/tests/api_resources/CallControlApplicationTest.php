<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\CallControlApplication
 */
final class CallControlApplicationTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/call_control_applications'
        );
        $resources = CallControlApplication::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\CallControlApplication::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/call_control_applications'
        );
        $resource = CallControlApplication::create([
            'application_name' => 'call-router',
            'webhook_event_url' => 'https://example.com'
        ]);
        $this->assertInstanceOf(\Telnyx\CallControlApplication::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = CallControlApplication::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/call_control_applications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\CallControlApplication::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/call_control_applications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = CallControlApplication::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\CallControlApplication::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/call_control_applications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = CallControlApplication::update(self::TEST_RESOURCE_ID, [
            'application_name' => 'call-router',
            'webhook_event_url' => 'https://example.com'
        ]);
        $this->assertInstanceOf(\Telnyx\CallControlApplication::class, $resource);
    }
}

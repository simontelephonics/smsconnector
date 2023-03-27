<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\FaxApplication
 */
final class FaxApplicationTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/fax_applications'
        );
        $resources = FaxApplication::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\FaxApplication::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/fax_applications'
        );
        $resource = FaxApplication::create([
            "application_name" => "call-router",
            "webhook_event_url" => "https://example.com"
        ]);
        $this->assertInstanceOf(\Telnyx\FaxApplication::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/fax_applications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = FaxApplication::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\FaxApplication::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            "patch",
            "/v2/fax_applications/" . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = FaxApplication::update(self::TEST_RESOURCE_ID, [
            "application_name" => "call-router",
            "webhook_event_url" => "https://example.com"
        ]);
        $this->assertInstanceOf(\Telnyx\FaxApplication::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = FaxApplication::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/fax_applications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\FaxApplication::class, $resource);
    }
}
